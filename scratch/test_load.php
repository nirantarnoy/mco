<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@common', dirname(__DIR__) . '/common');
Yii::setAlias('@backend', dirname(__DIR__) . '/backend');

echo "Loading PurchReq...\n";
try {
    if (class_exists('backend\models\PurchReq')) {
        echo "Class backend\models\PurchReq exists.\n";
        echo "Calling getNextPurchReqNo...\n";
        echo \backend\models\PurchReq::getNextPurchReqNo(1, 1);
    } else {
        echo "Class backend\models\PurchReq does NOT exist.\n";
        // Try to load the file manually to see if there's an error
        $file = dirname(__DIR__) . '/backend/models/PurchReq.php';
        if (file_exists($file)) {
            echo "File exists: $file\n";
            require($file);
            echo "File required successfully.\n";
            if (class_exists('backend\models\PurchReq')) {
                echo "Class now exists after manual require.\n";
            }
        } else {
            echo "File does NOT exist: $file\n";
        }
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
