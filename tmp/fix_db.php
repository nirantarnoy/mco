<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = [
    'id' => 'temp-console',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mco_db',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];

new yii\console\Application($config);

try {
    // Add column if not exists
    $cols = Yii::$app->db->createCommand("SHOW COLUMNS FROM temp_invoice_line LIKE 'product_code'")->queryOne();
    if (!$cols) {
        Yii::$app->db->createCommand("ALTER TABLE temp_invoice_line ADD COLUMN product_code VARCHAR(50) AFTER temp_invoice_id")->execute();
        echo "Column added successfully.\n";
    } else {
        echo "Column already exists.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
