<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

class GeminiAiService extends Component
{
    public $keyFile;
    public $projectId;
    public $location = 'us-central1';
    public $apiKey; // Added to prevent unknown property error from legacy main-local.php
    
    // Default model to use (Vertex AI uses 001/002 versions typically, or flash-latest)
    // gemini-1.5-flash-001 is stable on Vertex AI
    public $model = 'gemini-1.5-flash-001';

    private $_accessToken;
    
    public function processInvoice($filePath)
    {
        $token = $this->getAccessToken();

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
                    'role' => 'user',
                    'parts' => [
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $imageData
                            ]
                        ],
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json'
            ]
        ];

        // Vertex AI Endpoint
        $apiUrl = sprintf(
            'https://%s-aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/%s:generateContent',
            $this->location,
            $this->projectId,
            $this->location,
            $this->model
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
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
        Yii::info('Vertex AI Gemini Response: ' . substr($response, 0, 1000) . '...', 'ocr');

        if ($httpCode !== 200) {
            $message = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            throw new Exception('Vertex AI Error (HTTP ' . $httpCode . '): ' . $message);
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

    public function getAccessToken()
    {
        if ($this->_accessToken) {
            return $this->_accessToken;
        }

        $keyPath = $this->keyFile ? Yii::getAlias($this->keyFile) : null;
        
        if (!$keyPath || !file_exists($keyPath)) {
            $candidates = [
                Yii::getAlias('@backend/config/vision-key.json'),
                Yii::getAlias('@backend/config/google-vision-key.json'),
                Yii::getAlias('@common/config/vision-key.json'),
                Yii::getAlias('@common/config/google-vision-key.json'),
            ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    $keyPath = $candidate;
                    break;
                }
            }
        }
        
        if (!$keyPath || !file_exists($keyPath)) {
            throw new Exception('Service Account Key file not found: ' . ($keyPath ?: '@backend/config/vision-key.json'));
        }

        $keyData = Json::decode(file_get_contents($keyPath));
        
        // Generate JWT
        $header = $this->base64url_encode(Json::encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now = time();
        $payload = $this->base64url_encode(Json::encode([
            'iss' => $keyData['client_email'],
            'sub' => $keyData['client_email'],
            'aud' => $keyData['token_uri'],
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform'
        ]));

        $signatureInput = $header . "." . $payload;
        $signature = '';
        if (!openssl_sign($signatureInput, $signature, $keyData['private_key'], 'SHA256')) {
            throw new Exception('Failed to sign JWT: ' . openssl_error_string());
        }
        $jwt = $signatureInput . "." . $this->base64url_encode($signature);

        // Exchange JWT for Access Token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $keyData['token_uri']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $tokenData = Json::decode($response);
        if (!isset($tokenData['access_token'])) {
            throw new Exception('Failed to obtain access token: ' . ($tokenData['error_description'] ?? 'Unknown Error'));
        }

        $this->_accessToken = $tokenData['access_token'];
        return $this->_accessToken;
    }

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
