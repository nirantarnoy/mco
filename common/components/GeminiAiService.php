<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

class GeminiAiService extends Component
{
    // API Key (Supports OpenRouter keys starting with sk-or-v1- OR Google AI Studio keys starting with AQ... / AIza...)
    public $apiKey;
    
    // Default model (OpenRouter or Google Native)
    public $model = 'gemini-3.6-flash';

    public function processInvoice($filePath)
    {
        $prompt = 'You are an expert invoice parser. Extract the data from this Thai invoice and return ONLY a valid JSON object. Do not include markdown formatting or backticks around the JSON. The JSON must exactly follow this schema:
{
    "vendor_name": "Name of the supplier/vendor",
    "customer_name": "Name of the customer/buyer",
    "customer_tax_id": "13-digit tax id of the customer",
    "invoice_number": "Invoice number",
    "invoice_date": "Date of invoice in YYYY-MM-DD format",
    "subtotal": 0.00,
    "vat_amount": 0.00,
    "total_amount": 0.00,
    "line_items": [
        {
            "product_code": "Barcode or item code",
            "description": "Item name/description",
            "quantity": 1,
            "unit": "Unit of measure",
            "unit_price": 0.00,
            "amount": 0.00
        }
    ]
}
If a value is not found, use null for strings and 0 for numbers. Ensure the JSON is well-formed.';

        return $this->executeAiRequest($filePath, $prompt, 'Invoice AI');
    }

    public function processBankBook($filePath)
    {
        $prompt = 'You are an expert Thai bank document and passbook parser. Extract the bank account information from this image and return ONLY a valid JSON object. Do not include markdown formatting or backticks around the JSON.
The JSON must follow this exact schema:
{
    "account_number": "Bank account number (digits only or formatted with dashes e.g. 123-4-56789-0)",
    "bank_name": "Name of the bank in Thai or English (e.g. กสิกรไทย / KBank, ไทยพาณิชย์ / SCB, กรุงเทพ / Bangkok Bank, กรุงไทย / KTB, etc.)",
    "account_name": "Account holder name / Account title"
}
If a field cannot be found in the image, use null for that field. Ensure the JSON is well-formed.';

        return $this->executeAiRequest($filePath, $prompt, 'MCO Vendor Bank Scanner');
    }

    protected function executeAiRequest($filePath, $prompt, $title)
    {
        if (empty($this->apiKey)) {
            throw new Exception('Gemini AI API Key is missing. Please configure it in common/config/main-local.php.');
        }

        if (!file_exists($filePath)) {
            throw new Exception('File not found: ' . $filePath);
        }

        $imageData = base64_encode(file_get_contents($filePath));
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = 'image/jpeg';
        if ($ext === 'png') $mimeType = 'image/png';
        if ($ext === 'webp') $mimeType = 'image/webp';
        if ($ext === 'pdf') {
            throw new Exception('Vision models currently support images (JPG, PNG, WEBP). Please convert PDF to image before sending.');
        }

        $key = trim($this->apiKey);

        // Check if using OpenRouter API key vs Google AI Studio Native key
        if (strpos($key, 'sk-or-v1-') === 0) {
            return $this->callOpenRouterApi($key, $prompt, $imageData, $mimeType, $title);
        } else {
            return $this->callGoogleGeminiApi($key, $prompt, $imageData, $mimeType);
        }
    }

    protected function callOpenRouterApi($key, $prompt, $imageData, $mimeType, $title)
    {
        $modelName = (strpos($this->model, '/') !== false) ? $this->model : 'google/gemini-2.5-flash';
        $payload = [
            'model' => $modelName,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        [
                            'type' => 'image_url',
                            'image_url' => ['url' => 'data:' . $mimeType . ';base64,' . $imageData]
                        ]
                    ]
                ]
            ]
        ];

        $hostInfo = (Yii::$app->has('request') && method_exists(Yii::$app->request, 'getHostInfo')) ? Yii::$app->request->getHostInfo() : 'http://localhost';

        $maxRetries = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
                'HTTP-Referer: ' . $hostInfo,
                'X-Title: ' . $title
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error) {
                $lastException = new Exception('OpenRouter CURL Error: ' . $error);
                usleep(500000);
                continue;
            }

            $result = Json::decode($response);

            if ($httpCode === 200) {
                $text = $result['choices'][0]['message']['content'] ?? '';
                return $this->parseJsonResponse($text);
            }

            $message = $result['error']['message'] ?? 'Unknown API Error';
            
            // Retry on temporary server errors / rate limits (503, 429, 500, 502, 504)
            if (in_array($httpCode, [503, 429, 500, 502, 504])) {
                $lastException = new Exception("OpenRouter API Error (HTTP {$httpCode}): {$message}");
                sleep($attempt);
                continue;
            }

            throw new Exception("OpenRouter API Error (HTTP {$httpCode}): {$message}");
        }

        throw $lastException ?: new Exception('OpenRouter API Error: Unable to connect after retries.');
    }

    protected function callGoogleGeminiApi($key, $prompt, $imageData, $mimeType)
    {
        $modelList = ['gemini-3.6-flash', 'gemini-2.5-flash', 'gemini-1.5-flash'];
        if (!empty($this->model) && strpos($this->model, '/') === false) {
            array_unshift($modelList, $this->model);
            $modelList = array_unique($modelList);
        }

        $lastException = null;
        $maxRetriesPerModel = 3;

        foreach ($modelList as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($key);

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            for ($attempt = 1; $attempt <= $maxRetriesPerModel; $attempt++) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);

                $response = curl_exec($ch);
                $error = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($error) {
                    $lastException = new Exception('Google Gemini CURL Error: ' . $error);
                    usleep(500000);
                    continue;
                }

                $result = Json::decode($response);

                if ($httpCode === 200) {
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    return $this->parseJsonResponse($text);
                }

                $message = $result['error']['message'] ?? 'Unknown API Error';

                // Retry on HTTP 503 (High demand), 429 (Rate limit), 500, 502, 504
                if (in_array($httpCode, [503, 429, 500, 502, 504])) {
                    $lastException = new Exception("เซิร์ฟเวอร์ Google Gemini กำลังมีผู้ใช้งานหนาแน่น (HTTP {$httpCode}): {$message}");
                    sleep($attempt); // Wait 1s, 2s, 3s
                    continue;
                }

                // If 404 (Model deprecated), break loop to try next model in modelList
                if ($httpCode === 404) {
                    $lastException = new Exception("Google Gemini Model {$modelName} not found (HTTP 404): {$message}");
                    break;
                }

                throw new Exception("Google Gemini API Error (HTTP {$httpCode}): {$message}");
            }
        }

        throw $lastException ?: new Exception('เซิร์ฟเวอร์ Google Gemini มีผู้ใช้งานหนาแน่นชั่วคราว กรุณากดลองใหม่อีกครั้งในอีกสักครู่');
    }

    protected function parseJsonResponse($text)
    {
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/```$/', '', $text);
        $text = trim($text);

        try {
            $jsonParsed = Json::decode($text);
            return [
                'success' => true,
                'data' => $jsonParsed,
                'rawText' => $text
            ];
        } catch (\Exception $e) {
            throw new Exception('Failed to parse Gemini response as JSON: ' . $text);
        }
    }
}
