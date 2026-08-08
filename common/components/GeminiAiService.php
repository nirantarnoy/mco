<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

class GeminiAiService extends Component
{
    // API Key for OpenRouter
    public $apiKey;
    
    // Default model on OpenRouter
    // You can change this to "openai/gpt-4o", "anthropic/claude-3.5-sonnet", etc.
    public $model = 'google/gemini-1.5-flash';
    
    public function processInvoice($filePath)
    {
        if (empty($this->apiKey)) {
            throw new Exception('OpenRouter API Key is missing. Please configure it in common/config/main-local.php.');
        }

        if (!file_exists($filePath)) {
            throw new Exception('File not found: ' . $filePath);
        }

        $imageData = base64_encode(file_get_contents($filePath));
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = 'image/jpeg';
        if ($ext === 'png') $mimeType = 'image/png';
        if ($ext === 'pdf') {
            throw new Exception('OpenRouter Vision models currently support images (JPG, PNG). Please convert PDF to image before sending.');
        }

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

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:' . $mimeType . ';base64,' . $imageData
                            ]
                        ]
                    ]
                ]
            ],
            // 'response_format' => ['type' => 'json_object'], // Supported by many models on OpenRouter
        ];

        $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: ' . Yii::$app->request->hostInfo, 
            'X-Title: Billora AI'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }

        $result = Json::decode($response);
        Yii::info('OpenRouter Response: ' . substr($response, 0, 1000) . '...', 'ocr');

        if ($httpCode !== 200) {
            $message = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            throw new Exception('OpenRouter API Error (HTTP ' . $httpCode . '): ' . $message);
        }
        
        $text = $result['choices'][0]['message']['content'] ?? '';
        
        // Remove markdown block if model still returns it
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
            throw new Exception('Failed to parse OpenRouter response as JSON: ' . $text);
        }
    }
}
