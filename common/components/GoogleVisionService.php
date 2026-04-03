<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * GoogleVisionService is a service component to interact with Google Vision API using Service Account.
 */
class GoogleVisionService extends Component
{
    /**
     * @var string Path to Google Cloud Service Account JSON Key file
     */
    public $keyFile;

    /**
     * @var string API Endpoint
     */
    public $apiUrl = 'https://vision.googleapis.com/v1/images:annotate';

    /**
     * @var string|null Cached access token
     */
    private $_accessToken;

    /**
     * Perform OCR on an image file.
     * @param string $filePath Absolute path to the image file.
     * @return array OCR results (full text and segments)
     * @throws Exception
     */
    public function scanText($filePath)
    {
        $token = $this->getAccessToken();
        
        if (!file_exists($filePath)) {
            throw new Exception('File not found: ' . $filePath);
        }

        $imageData = base64_encode(file_get_contents($filePath));

        $payload = [
            'requests' => [
                [
                    'image' => [
                        'content' => $imageData
                    ],
                    'features' => [
                        [
                            'type' => 'DOCUMENT_TEXT_DETECTION' // ปรับให้เหมาะกับการสแกนเอกสารมากกว่า
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // เพิ่ม timeout

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }

        $result = Json::decode($response);
        
        // บันทึก Log สำหรับการตรวจสอบ (สามารถดูได้ที่ runtime/logs/app.log)
        Yii::info('Google Vision Response: ' . $response, 'ocr');

        if ($httpCode !== 200) {
            $message = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            throw new Exception('Google Vision API Error (HTTP ' . $httpCode . '): ' . $message);
        }

        return $this->parseResult($result);
    }

    /**
     * Get OAuth2 Access Token using Service Account JWT.
     * @return string
     * @throws Exception
     */
    public function getAccessToken()
    {
        if ($this->_accessToken) {
            return $this->_accessToken;
        }

        $keyPath = $this->keyFile ? Yii::getAlias($this->keyFile) : Yii::getAlias('@common/config/google-vision-key.json');
        
        if (!file_exists($keyPath)) {
            throw new Exception('Service Account Key file not found: ' . $keyPath);
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
            'scope' => 'https://www.googleapis.com/auth/cloud-vision'
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

    /**
     * Parse the API result structure.
     */
    protected function parseResult($result)
    {
        $fullText = '';
        $details = [];

        if (isset($result['responses'][0]['fullTextAnnotation'])) {
            $fullText = $result['responses'][0]['fullTextAnnotation']['text'];
        } elseif (isset($result['responses'][0]['textAnnotations'])) {
            $fullText = $result['responses'][0]['textAnnotations'][0]['description'] ?? '';
        }

        if (isset($result['responses'][0]['textAnnotations'])) {
            $details = $result['responses'][0]['textAnnotations'];
        }

        return [
            'fullText' => $fullText,
            'details' => $details,
            'raw' => $result
        ];
    }

    /**
     * Base64 URL Safe encoding helper
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
