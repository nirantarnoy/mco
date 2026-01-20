<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = [
    'id' => 'basic-console',
    'basePath' => __DIR__,
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mco',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];
new yii\console\Application($config);

$table = Yii::$app->db->getTableSchema('action_logs');
if ($table) {
    foreach ($table->columns as $column) {
        echo $column->name . " (" . $column->dbType . ")\n";
    }
} else {
    echo "Table action_logs not found\n";
}
