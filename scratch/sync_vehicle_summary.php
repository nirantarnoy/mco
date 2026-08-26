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

// 1. Fetch CSV from Google Sheet Tab 1 (สรุปยอดค่าใช้จ่ายราย JOB No)
$url = 'https://docs.google.com/spreadsheets/d/1ICudBNBaXrujPiSiNV2oDjA4QY6CUNrogbyBfYyy_XA/export?format=csv';

echo "=======================================================\n";
echo "Starting Vehicle Expense Data Clear & Re-Import Script\n";
echo "Source: Google Sheet (สรุปยอดค่าใช้จ่ายราย JOB No)\n";
echo "URL: $url\n";
echo "=======================================================\n\n";

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
    die("Error: Cannot download CSV from Google Sheets!\n");
}

// Remove BOM
$content = str_replace("\xEF\xBB\xBF", '', $content);

$tempFile = tempnam(sys_get_temp_dir(), 'gsheet_job_');
file_put_contents($tempFile, $content);

$handle = fopen($tempFile, 'r');
if ($handle === false) {
    die("Error: Cannot open temp CSV file!\n");
}

$transaction = Yii::$app->db->beginTransaction();

try {
    // Clear old vehicle_expense data
    echo "[Step 1] Clearing existing records in vehicle_expense table...\n";
    $deletedCount = \backend\models\VehicleExpense::deleteAll();
    echo "  -> Deleted $deletedCount old records.\n\n";

    echo "[Step 2] Importing new summary records from Google Sheet...\n";
    $batchId = 'job_summary_' . date('YmdHis');
    $rowIndex = 0;
    $successCount = 0;
    $skippedCount = 0;

    while (($data = fgetcsv($handle, 10000, ',')) !== false) {
        $rowIndex++;

        // Skip Header (Row 1)
        if ($rowIndex == 1) {
            continue;
        }

        $jobNoRaw = trim($data[0] ?? '');
        $countRaw = trim($data[1] ?? '');
        $distRaw = trim($data[2] ?? '');
        $costRaw = trim($data[5] ?? '');
        $passengerRaw = trim($data[6] ?? '');
        $wageRaw = trim($data[7] ?? '');

        // Skip summary / total row
        if (mb_stripos($jobNoRaw, 'ผลรวม') !== false || mb_stripos($jobNoRaw, 'รวม') !== false || mb_stripos($jobNoRaw, 'total') !== false) {
            $skippedCount++;
            continue;
        }

        if (empty($jobNoRaw)) {
            $skippedCount++;
            continue;
        }

        $totalDistance = abs(floatval(str_replace(',', '', $distRaw)));
        $vehicleCost = abs(floatval(str_replace(',', '', $costRaw)));
        $passengerCount = intval(str_replace(',', '', $passengerRaw));
        $totalWage = abs(floatval(str_replace(',', '', $wageRaw)));

        $model = new \backend\models\VehicleExpense();
        $model->expense_date = date('Y-m-d');
        $model->job_no = $jobNoRaw;
        $model->vehicle_no = 'สรุปราย JOB';
        $model->job_description = "สรุปยอดค่าใช้จ่ายราย JOB No จาก Google Sheet ({$jobNoRaw})";
        $model->total_distance = $totalDistance;
        $model->vehicle_cost = $vehicleCost;
        $model->passenger_count = $passengerCount;
        $model->total_wage = $totalWage;
        $model->import_batch = $batchId;

        if ($model->save(false)) {
            $successCount++;
            echo "  [+] Row {$rowIndex}: JobNo='{$jobNoRaw}' | Distance={$totalDistance} km | Cost={$vehicleCost} THB | Wage={$totalWage} THB\n";
        }
    }

    fclose($handle);
    @unlink($tempFile);

    $transaction->commit();

    echo "\n=======================================================\n";
    echo "SUCCESS: Cleared $deletedCount old records and imported $successCount summary records.\n";
    echo "Skipped: $skippedCount rows.\n";
    echo "Batch ID: $batchId\n";
    echo "=======================================================\n";

} catch (\Exception $e) {
    if (isset($handle) && is_resource($handle)) {
        fclose($handle);
    }
    if (isset($tempFile) && file_exists($tempFile)) {
        @unlink($tempFile);
    }
    $transaction->rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
}
