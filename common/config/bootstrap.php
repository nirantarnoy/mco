<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');

Yii::$classMap['app\behaviors\ActionLogBehavior'] = dirname(dirname(__DIR__)) . '/backend/behaviors/ActionLogBehavior.php';
Yii::$classMap['backend\behaviors\ActionLogBehavior'] = dirname(dirname(__DIR__)) . '/backend/behaviors/ActionLogBehavior.php';
