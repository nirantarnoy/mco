<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

class GeminiAiService extends Component
{
    public $apiKey;
    // Default model to use
    public $model = 'gemini-2.5-flash';
    
    public function processInvoice($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('File not found: ' . $filePath);
        }

        $imageData = base64_encode(file_get_contents($filePath));
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = 'image/jpeg';
        if ($ext === 'png') $mimeType = 'image/png';
        if ($ext === 'pdf') $mimeType = 'application/pdf';

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
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $imageData
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json'
            ]
        ];

        $apiUrl = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->model,
            $this->apiKey
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
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
        Yii::info('Gemini AI Response: ' . substr($response, 0, 1000) . '...', 'ocr');

        if ($httpCode !== 200) {
            $message = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            throw new Exception('Gemini AI API Error (HTTP ' . $httpCode . '): ' . $message);
        }
        
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Remove markdown block if Gemini still returns it despite the instruction
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
