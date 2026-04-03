<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\Html;

/**
 * OcrController handles Google Vision OCR tasks.
 */
class OcrController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Process the OCR request.
     * @return array|string|Response
     */
    public function actionProcess()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $file = UploadedFile::getInstanceByName('ocr_file');
        if (!$file) {
            return [
                'success' => false,
                'message' => 'กรุณาแนบไฟล์รูปภาพ'
            ];
        }

        // Validate file type
        if (!in_array($file->extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
            return [
                'success' => false,
                'message' => 'รองรับเฉพาะไฟล์ JPG, JPEG, PNG และ PDF'
            ];
        }

        // Save file temporarily
        $tempDir = Yii::getAlias('@runtime/ocr');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $filePath = $tempDir .  '/' . time() . '_' . $file->baseName . '.' . $file->extension;
        
        if ($file->saveAs($filePath)) {
            try {
                $service = Yii::$app->googleVision;
                // You can get API Key from params if desired:
                // $service->apiKey = Yii::$app->params['googleVisionApiKey'];
                
                $result = $service->scanText($filePath);
                
                // OCR result clean up
                unlink($filePath);

                return [
                    'success' => true,
                    'fullText' => $result['fullText'],
                    'details' => $result['details']
                ];

            } catch (\Exception $e) {
                if (file_exists($filePath)) unlink($filePath);
                return [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'ไม่สามารถบันทึกไฟล์ได้'
        ];
    }
}
