<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../backend/config/main.php',
    require __DIR__ . '/../backend/config/main-local.php'
);

$application = new yii\web\Application($config);

// Google Sheet URL without gid (defaults to first tab: สรุปยอดค่าใช้จ่ายราย JOB No)
$url = 'https://docs.google.com/spreadsheets/d/1ICudBNBaXrujPiSiNV2oDjA4QY6CUNrogbyBfYyy_XA/export?format=csv';

echo "Fetching CSV from: $url\n\n";

$ctx = stream_context_create([
    'http' => [
        'timeout' => 30,
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$content = @file_get_contents($url, false, $ctx);
if ($content === false) {
    echo "file_get_contents failed, trying curl...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $content = curl_exec($ch);
    curl_close($ch);
}

if ($content === false) {
    die("Failed to fetch Google Sheet CSV!\n");
}

// Remove BOM
$content = str_replace("\xEF\xBB\xBF", '', $content);

$tempFile = tempnam(sys_get_temp_dir(), 'gsheet_job_');
file_put_contents($tempFile, $content);

$handle = fopen($tempFile, 'r');
if ($handle === false) {
    die("Failed to open temp CSV file.\n");
}

$rowIndex = 0;
while (($data = fgetcsv($handle, 10000, ',')) !== false) {
    $rowIndex++;
    echo "Row $rowIndex: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
}
fclose($handle);
@unlink($tempFile);
