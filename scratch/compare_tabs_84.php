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

$targetJobNo = 'RY-QT26-000084';

echo "========================================================================================\n";
echo "COMPARISON CALCULATION REPORT FOR JOB: {$targetJobNo}\n";
echo "========================================================================================\n\n";

function fetchCsv($url) {
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
        return false;
    }

    $content = str_replace("\xEF\xBB\xBF", '', $content);
    if (!mb_check_encoding($content, 'UTF-8')) {
        $converted = @iconv('Windows-874', 'UTF-8//IGNORE', $content);
        if ($converted !== false) {
            $content = $converted;
        }
    }

    $tempFile = tempnam(sys_get_temp_dir(), 'cmp_');
    file_put_contents($tempFile, $content);
    return $tempFile;
}

// ---------------------------------------------------------
// 1. Tab 1: สรุปยอดค่าใช้จ่ายราย JOB No
// ---------------------------------------------------------
echo "[PART 1] Reading Tab 1 (สรุปยอดค่าใช้จ่ายราย JOB No)...\n";
$urlTab1 = 'https://docs.google.com/spreadsheets/d/1ICudBNBaXrujPiSiNV2oDjA4QY6CUNrogbyBfYyy_XA/export?format=csv';
$fileTab1 = fetchCsv($urlTab1);

$tab1Data = null;
if ($fileTab1) {
    $h = fopen($fileTab1, 'r');
    $rowIdx = 0;
    while (($data = fgetcsv($h, 10000, ',')) !== false) {
        $rowIdx++;
        $jobNo = trim($data[0] ?? '');
        if (strcasecmp($jobNo, $targetJobNo) === 0) {
            $tab1Data = [
                'job_no' => $jobNo,
                'count' => intval(str_replace(',', '', trim($data[1] ?? 0))),
                'distance' => floatval(str_replace(',', '', trim($data[2] ?? 0))),
                'fuel_cost' => floatval(str_replace(',', '', trim($data[3] ?? 0))),
                'vehicle_cost' => floatval(str_replace(',', '', trim($data[5] ?? 0))),
                'passenger_count' => intval(str_replace(',', '', trim($data[6] ?? 0))),
                'wage' => floatval(str_replace(',', '', trim($data[7] ?? 0))),
                'total_expense' => floatval(str_replace(',', '', trim($data[8] ?? 0))),
            ];
            break;
        }
    }
    fclose($h);
    @unlink($fileTab1);
}

if ($tab1Data) {
    echo "  -> Found in Tab 1:\n";
    echo "     - จำนวนเที่ยว (COUNTA): {$tab1Data['count']} เที่ยว\n";
    echo "     - ระยะทางรวม: " . number_format($tab1Data['distance'], 1) . " กม.\n";
    echo "     - ค่าใช้จ่ายรถ (x5 บ.): " . number_format($tab1Data['vehicle_cost'], 2) . " บาท\n";
    echo "     - ค่าจ้างรวม (Wage): " . number_format($tab1Data['wage'], 2) . " บาท\n";
    echo "     - รวมค่าใช้จ่ายทั้งหมด: " . number_format($tab1Data['total_expense'], 2) . " บาท\n\n";
} else {
    echo "  -> Not found in Tab 1!\n\n";
}

// ---------------------------------------------------------
// 2. Tab 2: สรุปยอดค่าใช้จ่ายรายวัน (gid=952154332)
// ---------------------------------------------------------
echo "[PART 2] Reading Tab 2 (สรุปยอดค่าใช้จ่ายรายวัน, gid=952154332)...\n";
$urlTab2 = 'https://docs.google.com/spreadsheets/d/1ICudBNBaXrujPiSiNV2oDjA4QY6CUNrogbyBfYyy_XA/export?format=csv&gid=952154332';
$fileTab2 = fetchCsv($urlTab2);

$tab2Rows = [];
$abnormalRows = [];

if ($fileTab2) {
    $h = fopen($fileTab2, 'r');
    $rowIdx = 0;
    $currentDate = null;
    $currentJobNo = null;

    while (($data = fgetcsv($h, 10000, ',')) !== false) {
        $rowIdx++;
        if ($rowIdx == 1) continue;

        $colA = trim($data[0] ?? '');
        $colB = trim($data[1] ?? '');
        $colC = trim($data[2] ?? '');
        $colD = trim($data[3] ?? '');
        $colE = trim($data[4] ?? '');
        $colF = trim($data[5] ?? '');
        $colG = trim($data[6] ?? '');

        // Skip total rows
        if (mb_stripos($colA, 'รวม') !== false || mb_stripos($colB, 'รวม') !== false || mb_stripos($colC, 'รวม') !== false) {
            continue;
        }

        if (!empty($colA)) {
            $currentDate = $colA;
            if (!empty($colB)) $currentJobNo = $colB;
        } else {
            if (!empty($colB)) $currentJobNo = $colB;
        }

        if (empty($currentJobNo)) continue;

        if (strcasecmp(trim($currentJobNo), $targetJobNo) === 0) {
            $dist = floatval(str_replace(',', '', $colD));
            $cost = floatval(str_replace(',', '', $colE));
            $pass = intval(str_replace(',', '', $colF));
            $wage = floatval(str_replace(',', '', $colG));

            $item = [
                'row_idx' => $rowIdx,
                'date' => $currentDate,
                'job_no' => $currentJobNo,
                'vehicle_no' => $colC,
                'distance' => $dist,
                'vehicle_cost' => $cost,
                'passenger_count' => $pass,
                'wage' => $wage,
            ];
            $tab2Rows[] = $item;

            if ($dist < 0 || $cost < 0 || $dist > 1000) {
                $abnormalRows[] = $item;
            }
        }
    }
    fclose($h);
    @unlink($fileTab2);
}

$tab2Count = count($tab2Rows);
echo "  -> Found {$tab2Count} daily records in Tab 2 for {$targetJobNo}\n\n";

$rawDistSum = 0;
$rawCostSum = 0;
$rawWageSum = 0;

$posDistSum = 0;
$posCostSum = 0;
$posWageSum = 0;

$absDistSum = 0;
$absCostSum = 0;
$absWageSum = 0;

foreach ($tab2Rows as $r) {
    $rawDistSum += $r['distance'];
    $rawCostSum += $r['vehicle_cost'];
    $rawWageSum += $r['wage'];

    if ($r['distance'] > 0) $posDistSum += $r['distance'];
    if ($r['vehicle_cost'] > 0) $posCostSum += $r['vehicle_cost'];
    if ($r['wage'] > 0) $posWageSum += $r['wage'];

    $absDistSum += abs($r['distance']);
    $absCostSum += abs($r['vehicle_cost']);
    $absWageSum += abs($r['wage']);
}

echo "--- TAB 2 CALCULATION RESULTS ---\n";
echo "1. RAW SUM (รวมตามตัวเลขจริงใน Sheet สรุปรายวัน ไม่ตัดติดลบ):\n";
echo "   - ระยะทางรวม: " . number_format($rawDistSum, 1) . " กม.\n";
echo "   - ค่าใช้จ่ายรถ (x5 บ.): " . number_format($rawDistSum * 5, 2) . " บาท (หรือคอลัมน์ค่ารถ: " . number_format($rawCostSum, 2) . " บาท)\n";
echo "   - ค่าจ้างรวม (Wage): " . number_format($rawWageSum, 2) . " บาท\n";
echo "   - ค่าใช้จ่ายรวมทั้งหมด: " . number_format(($rawDistSum * 5) + $rawWageSum, 2) . " บาท\n\n";

echo "2. POSITIVE ONLY SUM (รวมเฉพาะค่าที่เป็นบวก > 0):\n";
echo "   - ระยะทางรวม: " . number_format($posDistSum, 1) . " กม.\n";
echo "   - ค่าใช้จ่ายรถ (x5 บ.): " . number_format($posDistSum * 5, 2) . " บาท (หรือคอลัมน์ค่ารถ: " . number_format($posCostSum, 2) . " บาท)\n";
echo "   - ค่าจ้างรวม (Wage): " . number_format($posWageSum, 2) . " บาท\n";
echo "   - ค่าใช้จ่ายรวมทั้งหมด: " . number_format(($posDistSum * 5) + $posWageSum, 2) . " บาท\n\n";

echo "3. ABSOLUTE VALUE SUM (แปลงค่าติดลบเป็นบวก abs):\n";
echo "   - ระยะทางรวม: " . number_format($absDistSum, 1) . " กม.\n";
echo "   - ค่าใช้จ่ายรถ (x5 บ.): " . number_format($absDistSum * 5, 2) . " บาท (หรือคอลัมน์ค่ารถ: " . number_format($absCostSum, 2) . " บาท)\n";
echo "   - ค่าจ้างรวม (Wage): " . number_format($absWageSum, 2) . " บาท\n";
echo "   - ค่าใช้จ่ายรวมทั้งหมด: " . number_format(($absDistSum * 5) + $absWageSum, 2) . " บาท\n\n";

if (!empty($abnormalRows)) {
    echo "--- ABNORMAL/NEGATIVE ROWS FOUND IN TAB 2 (" . count($abnormalRows) . " rows) ---\n";
    foreach ($abnormalRows as $ab) {
        echo "   Row {$ab['row_idx']} | Date: {$ab['date']} | Vehicle: {$ab['vehicle_no']} | Dist: {$ab['distance']} km | Cost: {$ab['vehicle_cost']} THB | Wage: {$ab['wage']} THB\n";
    }
    echo "\n";
}

echo "========================================================================================\n";
echo "COMPARISON SUMMARY TABLE\n";
echo "========================================================================================\n";
printf("%-30s | %-12s | %-16s | %-16s | %-16s\n", "Source", "Records", "Distance (km)", "Vehicle Cost (THB)", "Wage (THB)");
echo "----------------------------------------------------------------------------------------\n";
if ($tab1Data) {
    printf("%-30s | %-12s | %-16s | %-16s | %-16s\n", "Tab 1 (สรุปราย JOB No)", $tab1Data['count'], number_format($tab1Data['distance'], 1), number_format($tab1Data['vehicle_cost'], 2), number_format($tab1Data['wage'], 2));
}
printf("%-30s | %-12s | %-16s | %-16s | %-16s\n", "Tab 2 (Raw Sum)", $tab2Count, number_format($rawDistSum, 1), number_format($rawDistSum * 5, 2), number_format($rawWageSum, 2));
printf("%-30s | %-12s | %-16s | %-16s | %-16s\n", "Tab 2 (Positive Only > 0)", $tab2Count, number_format($posDistSum, 1), number_format($posDistSum * 5, 2), number_format($posWageSum, 2));
printf("%-30s | %-12s | %-16s | %-16s | %-16s\n", "Tab 2 (Absolute Val abs)", $tab2Count, number_format($absDistSum, 1), number_format($absDistSum * 5, 2), number_format($absWageSum, 2));
echo "========================================================================================\n";
