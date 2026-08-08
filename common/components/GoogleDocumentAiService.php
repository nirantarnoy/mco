<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * GoogleDocumentAiService is a service component to interact with Google Document AI using REST API.
 */
class GoogleDocumentAiService extends Component
{
    public $keyFile;
    public $projectId;
    public $location = 'us';
    public $processorId;

    private $_accessToken;

    public function processDocument($filePath)
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

        $payload = [
            'skipHumanReview' => true,
            'rawDocument' => [
                'content' => $imageData,
                'mimeType' => $mimeType
            ]
        ];

        // Format: https://{location}-documentai.googleapis.com/v1/projects/{projectId}/locations/{location}/processors/{processorId}:process
        $apiUrl = sprintf(
            'https://%s-documentai.googleapis.com/v1/projects/%s/locations/%s/processors/%s:process',
            $this->location,
            $this->projectId,
            $this->location,
            $this->processorId
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
        Yii::info('Google Document AI Response: ' . substr($response, 0, 1000) . '...', 'ocr');

        if ($httpCode !== 200) {
            $message = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            throw new Exception('Document AI API Error (HTTP ' . $httpCode . '): ' . $message);
        }

        return $this->parseResult($result);
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
            'scope' => 'https://www.googleapis.com/auth/cloud-platform' // Scope for Document AI
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

    protected function parseResult($result)
    {
        // Convert Document AI response to a standardized format
        $document = $result['document'] ?? [];
        $text = $document['text'] ?? '';
        $entities = $document['entities'] ?? [];

        $parsedEntities = [];
        $lineItems = [];

        foreach ($entities as $entity) {
            $type = $entity['type'] ?? '';
            $mentionText = $entity['mentionText'] ?? '';
            
            // Extract standard fields
            if ($type === 'line_item') {
                $itemProps = $entity['properties'] ?? [];
                $item = [];
                foreach ($itemProps as $prop) {
                    $pType = $prop['type'] ?? '';
                    $item[$pType] = $prop['mentionText'] ?? '';
                }
                $lineItems[] = $item;
            } else {
                $parsedEntities[$type] = $mentionText;
            }
        }

        return [
            'fullText' => $text,
            'entities' => $parsedEntities,
            'lineItems' => $lineItems,
            'raw' => $result
        ];
    }

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
