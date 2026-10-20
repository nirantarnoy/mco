<?php
/**
 * Script สำหรับดึงข้อมูล จังหวัด อำเภอ ตำบล รหัสไปรษณีย์ ล่าสุดจาก GitHub
 * (โครงการ kongvut/thai-province-data) และบันทึกลงฐานข้อมูล mco_db
 */

set_time_limit(600);
ini_set('memory_limit', '1024M');

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'mco_db';

// ดึง config ฐานข้อมูลจาก Yii อัตโนมัติ (หากมี) เพื่อไม่ต้องแก้ไข user/password บน server
$configFile = __DIR__ . '/common/config/main-local.php';
if (file_exists($configFile)) {
    $config = require($configFile);
    if (isset($config['components']['db'])) {
        $dbConfig = $config['components']['db'];
        if (isset($dbConfig['username'])) $user = $dbConfig['username'];
        if (isset($dbConfig['password'])) $pass = $dbConfig['password'];
        if (isset($dbConfig['dsn'])) {
            if (preg_match('/host=([^;]+)/', $dbConfig['dsn'], $m)) $host = $m[1];
            if (preg_match('/dbname=([^;]+)/', $dbConfig['dsn'], $m)) $db = $m[1];
        }
    }
}

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . " (Host: $host, User: $user, DB: $db)\nกรุณาตรวจสอบการตั้งค่าฐานข้อมูลใน common/config/main-local.php หรือรันผ่านคำสั่ง 'php yii address/sync'\n");
}
mysqli_set_charset($conn, "utf8");

echo "=== เริ่มต้นสำรองข้อมูลและปรับโครงสร้างตาราง ===\n";

// 1. สำรองข้อมูลตารางเดิม
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `province_backup_" . date('Ymd_His') . "` AS SELECT * FROM `province`");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `amphur_backup_" . date('Ymd_His') . "` AS SELECT * FROM `amphur`");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `district_backup_" . date('Ymd_His') . "` AS SELECT * FROM `district`");
echo "สำรองข้อมูลตารางเดิมเรียบร้อยแล้ว\n";

// 2. ตรวจสอบและเพิ่มคอลัมน์ ZIPCODE ใน district หากยังไม่มี
$chk = mysqli_query($conn, "SHOW COLUMNS FROM `district` LIKE 'ZIPCODE'");
if (mysqli_num_rows($chk) == 0) {
    mysqli_query($conn, "ALTER TABLE `district` ADD COLUMN `ZIPCODE` varchar(5) NULL AFTER `DISTRICT_NAME`");
    mysqli_query($conn, "ALTER TABLE `district` ADD INDEX `idx_district_zipcode` (`ZIPCODE`)");
    echo "เพิ่มคอลัมน์ ZIPCODE ในตาราง district เรียบร้อยแล้ว\n";
}

// เพิ่ม index เพื่อความเร็วในการค้นหา
@mysqli_query($conn, "ALTER TABLE `district` ADD INDEX `idx_amphur_id` (`AMPHUR_ID`)");
@mysqli_query($conn, "ALTER TABLE `district` ADD INDEX `idx_province_id` (`PROVINCE_ID`)");
@mysqli_query($conn, "ALTER TABLE `amphur` ADD INDEX `idx_province_id` (`PROVINCE_ID`)");
@mysqli_query($conn, "ALTER TABLE `amphur` ADD INDEX `idx_postcode` (`POSTCODE`)");

// 3. ดาวน์โหลดข้อมูลจาก GitHub
echo "\n=== กำลังดาวน์โหลดข้อมูลที่อยู่ล่าสุดจาก GitHub ===\n";
$ctx = stream_context_create([
    'http' => ['method' => 'GET', 'header' => "User-Agent: PHP\r\n"],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$url_province = 'https://raw.githubusercontent.com/kongvut/thai-province-data/refs/heads/master/api/latest/province.json';
$url_district = 'https://raw.githubusercontent.com/kongvut/thai-province-data/refs/heads/master/api/latest/district.json';
$url_subdistrict = 'https://raw.githubusercontent.com/kongvut/thai-province-data/refs/heads/master/api/latest/sub_district.json';

echo "1) กำลังดาวน์โหลดข้อมูลจังหวัด (Provinces)...\n";
$json_p = file_get_contents($url_province, false, $ctx);
if (!$json_p) die("เกิดข้อผิดพลาดในการดาวน์โหลด province.json\n");
$provinces = json_decode($json_p, true);
echo "   ดาวน์โหลดสำเร็จ: " . count($provinces) . " จังหวัด\n";

echo "2) กำลังดาวน์โหลดข้อมูลอำเภอ/เขต (Districts/Amphurs)...\n";
$json_d = file_get_contents($url_district, false, $ctx);
if (!$json_d) die("เกิดข้อผิดพลาดในการดาวน์โหลด district.json\n");
$amphurs = json_decode($json_d, true);
echo "   ดาวน์โหลดสำเร็จ: " . count($amphurs) . " อำเภอ/เขต\n";

echo "3) กำลังดาวน์โหลดข้อมูลตำบล/แขวง (Sub-districts/Tambons)...\n";
$json_sd = file_get_contents($url_subdistrict, false, $ctx);
if (!$json_sd) die("เกิดข้อผิดพลาดในการดาวน์โหลด sub_district.json\n");
$subdistricts = json_decode($json_sd, true);
echo "   ดาวน์โหลดสำเร็จ: " . count($subdistricts) . " ตำบล/แขวง\n";

// 4. นำเข้าข้อมูลจังหวัด (province)
echo "\n=== อัปเดตตาราง province ===\n";
$prov_count = 0;
foreach ($provinces as $p) {
    $p_id = (int)$p['id'];
    $p_code = sprintf("%02d", $p['id']);
    $p_name = mysqli_real_escape_string($conn, trim($p['name']['th']));
    $geo_id = (int)($p['geography_id'] ?? 0);

    // ตรวจสอบว่ามี id นี้หรือชื่อนี้หรือยัง
    $chk = mysqli_query($conn, "SELECT PROVINCE_ID FROM `province` WHERE `PROVINCE_ID` = $p_id OR `PROVINCE_NAME` = '$p_name'");
    if ($row = mysqli_fetch_assoc($chk)) {
        $existing_id = $row['PROVINCE_ID'];
        mysqli_query($conn, "UPDATE `province` SET `PROVINCE_NAME` = '$p_name', `GEO_ID` = $geo_id WHERE `PROVINCE_ID` = $existing_id");
    } else {
        mysqli_query($conn, "INSERT INTO `province` (`PROVINCE_ID`, `PROVINCE_CODE`, `PROVINCE_NAME`, `GEO_ID`) VALUES ($p_id, '$p_code', '$p_name', $geo_id)");
    }
    $prov_count++;
}
echo "อัปเดตจังหวัดเรียบร้อย: $prov_count รายการ\n";

// 5. นำเข้าข้อมูลอำเภอ (amphur)
echo "\n=== อัปเดตตาราง amphur ===\n";
// เตรียม map รหัสไปรษณีย์เริ่มต้นจาก subdistrict
$amphur_postcode_map = [];
foreach ($subdistricts as $sd) {
    $aid = (int)$sd['district_id'];
    if (!empty($sd['zip_code']) && !isset($amphur_postcode_map[$aid])) {
        $amphur_postcode_map[$aid] = $sd['zip_code'];
    }
}

$amphur_count = 0;
foreach ($amphurs as $a) {
    $a_id = (int)$a['id'];
    $a_code = (string)$a['id'];
    $prefix = $a['prefix']['th'] ?? '';
    $a_name_clean = trim($a['name']['th']);
    // ถ้าชื่อยังไม่มีคำว่า เขต หรือ อำเภอ ให้ใส่ตาม prefix
    if (!empty($prefix) && strpos($a_name_clean, $prefix) !== 0) {
        $a_name = $prefix . $a_name_clean;
    } else {
        $a_name = $a_name_clean;
    }
    $a_name_esc = mysqli_real_escape_string($conn, $a_name);
    $p_id = (int)$a['province_id'];
    $postcode = mysqli_real_escape_string($conn, (string)($amphur_postcode_map[$a_id] ?? ''));

    $chk = mysqli_query($conn, "SELECT AMPHUR_ID FROM `amphur` WHERE `AMPHUR_ID` = $a_id");
    if (mysqli_num_rows($chk) > 0) {
        mysqli_query($conn, "UPDATE `amphur` SET `AMPHUR_NAME` = '$a_name_esc', `POSTCODE` = IF('$postcode' != '', '$postcode', `POSTCODE`), `PROVINCE_ID` = $p_id WHERE `AMPHUR_ID` = $a_id");
    } else {
        mysqli_query($conn, "INSERT INTO `amphur` (`AMPHUR_ID`, `AMPHUR_CODE`, `AMPHUR_NAME`, `POSTCODE`, `PROVINCE_ID`) VALUES ($a_id, '$a_code', '$a_name_esc', '$postcode', $p_id)");
    }
    $amphur_count++;
}
echo "อัปเดตอำเภอ/เขตเรียบร้อย: $amphur_count รายการ\n";

// 6. นำเข้าข้อมูลตำบล (district)
echo "\n=== อัปเดตตาราง district (ตำบล/แขวง) ===\n";
$district_count = 0;
// ใช้ INSERT ... ON DUPLICATE KEY UPDATE หรือ batch insert
$batch = [];
$total_sub = count($subdistricts);

foreach ($subdistricts as $idx => $sd) {
    $d_id = (int)$sd['id'];
    $d_code = (string)$sd['id'];
    $prefix = $sd['prefix']['th'] ?? '';
    $d_name_clean = trim($sd['name']['th']);
    // เก็บชื่อตำบลสะอาด (ไม่ใส่คำว่า ตำบล/แขวง นำหน้า เพื่อให้สอดคล้องกับของเดิมใน DB เช่น พระบรมมหาราชวัง)
    $d_name_esc = mysqli_real_escape_string($conn, $d_name_clean);
    $a_id = (int)$sd['district_id'];
    $zipcode = mysqli_real_escape_string($conn, (string)($sd['zip_code'] ?? ''));

    // หา province_id จาก amphur
    $p_id = isset($amphurs_map[$a_id]) ? (int)$amphurs_map[$a_id]['province_id'] : (int)substr((string)$a_id, 0, 2);

    $batch[] = "($d_id, '$d_code', '$d_name_esc', '$zipcode', $a_id, $p_id)";

    if (count($batch) >= 500 || $idx == $total_sub - 1) {
        $sql = "INSERT INTO `district` (`DISTRICT_ID`, `DISTRICT_CODE`, `DISTRICT_NAME`, `ZIPCODE`, `AMPHUR_ID`, `PROVINCE_ID`) 
                VALUES " . implode(',', $batch) . " 
                ON DUPLICATE KEY UPDATE 
                `DISTRICT_NAME` = VALUES(`DISTRICT_NAME`),
                `ZIPCODE` = VALUES(`ZIPCODE`),
                `AMPHUR_ID` = VALUES(`AMPHUR_ID`),
                `PROVINCE_ID` = VALUES(`PROVINCE_ID`)";
        if (!mysqli_query($conn, $sql)) {
            echo "Error batch: " . mysqli_error($conn) . "\n";
        }
        $district_count += count($batch);
        $batch = [];
    }
}
echo "อัปเดตตำบล/แขวงเรียบร้อย: $district_count รายการ\n";

// สรุปข้อมูลสุดท้ายในฐานข้อมูล
echo "\n=== สรุปผลการอัปเดต ===\n";
$p_total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `province`"))[0];
$a_total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `amphur`"))[0];
$d_total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `district`"))[0];
$z_total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `district` WHERE `ZIPCODE` IS NOT NULL AND `ZIPCODE` != ''"))[0];

echo "- จำนวนจังหวัดทั้งหมด: $p_total จังหวัด\n";
echo "- จำนวนอำเภอทั้งหมด: $a_total อำเภอ/เขต\n";
echo "- จำนวนตำบลทั้งหมด: $d_total ตำบล/แขวง\n";
echo "- จำนวนตำบลที่มีรหัสไปรษณีย์: $z_total รายการ\n";

mysqli_close($conn);
echo "\n=== เสร็จสิ้นกระบวนการอย่างสมบูรณ์ ===\n";
