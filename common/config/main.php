<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager'=>[
            'class' => 'yii\rbac\DbManager',
        ],
        'googleVision' => [
            'class' => 'common\components\GoogleVisionService',
            'keyFile' => '@backend/config/vision-key.json',
        ],
        'googleDocumentAi' => [
            'class' => 'common\components\GoogleDocumentAiService',
            'keyFile' => '@backend/config/vision-key.json',
            'projectId' => 'billora-ai',
            'location' => 'asia-southeast1',
            'processorId' => '9b311c68c9eb63b6',
        ],
        'geminiAi' => [
            'class' => 'common\components\GeminiAiService',
            'keyFile' => '@backend/config/vision-key.json',
            'projectId' => 'billora-ai',
            'location' => 'us-central1',
        ],
    ],
];
