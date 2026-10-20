<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * คำสั่ง Sync ข้อมูล จังหวัด อำเภอ ตำบล รหัสไปรษณีย์
 * ใช้งาน: php yii address/sync
 */
class AddressController extends Controller
{
    /**
     * ดึงข้อมูล จังหวัด อำเภอ ตำบล รหัสไปรษณีย์ ล่าสุดจาก GitHub และ sync ลงฐานข้อมูล
     */
    public function actionSync()
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        $db = Yii::$app->db;

        $this->stdout("=== เริ่มต้นสำรองข้อมูลและปรับโครงสร้างตาราง ===\n", Console::FG_YELLOW);

        // 1. สำรองข้อมูลตารางเดิม
        $backupSuffix = date('Ymd_His');
        try {
            $db->createCommand("CREATE TABLE IF NOT EXISTS `province_backup_{$backupSuffix}` AS SELECT * FROM `province`")->execute();
            $db->createCommand("CREATE TABLE IF NOT EXISTS `amphur_backup_{$backupSuffix}` AS SELECT * FROM `amphur`")->execute();
            $db->createCommand("CREATE TABLE IF NOT EXISTS `district_backup_{$backupSuffix}` AS SELECT * FROM `district`")->execute();
            $this->stdout("สำรองข้อมูลตารางเดิมเรียบร้อยแล้ว (_backup_{$backupSuffix})\n", Console::FG_GREEN);
        } catch (\Exception $e) {
            $this->stdout("คำเตือนการสำรองข้อมูล: " . $e->getMessage() . "\n", Console::FG_YELLOW);
        }

        // 2. ตรวจสอบและเพิ่มคอลัมน์ ZIPCODE ใน district หากยังไม่มี
        try {
            $colExists = $db->createCommand("SHOW COLUMNS FROM `district` LIKE 'ZIPCODE'")->queryOne();
            if (!$colExists) {
                $db->createCommand("ALTER TABLE `district` ADD COLUMN `ZIPCODE` varchar(5) NULL AFTER `DISTRICT_NAME`")->execute();
                $db->createCommand("ALTER TABLE `district` ADD INDEX `idx_district_zipcode` (`ZIPCODE`)")->execute();
                $this->stdout("เพิ่มคอลัมน์ ZIPCODE ในตาราง district เรียบร้อยแล้ว\n", Console::FG_GREEN);
            }
        } catch (\Exception $e) {
            $this->stdout("ข้อความคอลัมน์ ZIPCODE: " . $e->getMessage() . "\n", Console::FG_YELLOW);
        }

        // เพิ่ม indexes เพื่อประสิทธิภาพ
        $indexes = [
            "ALTER TABLE `district` ADD INDEX `idx_amphur_id` (`AMPHUR_ID`)",
            "ALTER TABLE `district` ADD INDEX `idx_province_id` (`PROVINCE_ID`)",
            "ALTER TABLE `amphur` ADD INDEX `idx_province_id` (`PROVINCE_ID`)",
            "ALTER TABLE `amphur` ADD INDEX `idx_postcode` (`POSTCODE`)"
        ];
        foreach ($indexes as $idxSql) {
            try {
                $db->createCommand($idxSql)->execute();
            } catch (\Exception $e) {
                // ข้ามถ้ามี index อยู่แล้ว
            }
        }

        // 3. ดาวน์โหลดข้อมูลจาก GitHub
        $this->stdout("\n=== กำลังดาวน์โหลดข้อมูลที่อยู่ล่าสุดจาก GitHub ===\n", Console::FG_YELLOW);
        $ctx = stream_context_create([
            'http' => ['method' => 'GET', 'header' => "User-Agent: PHP\r\n"],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);

        $url_province = 'https://raw.githubusercontent.com/kongvut/thai-province-data/refs/heads/master/api/latest/province.json';
        $url_district = 'https://raw.githubusercontent.com/kongvut/thai-province-data/refs/heads/master/api/latest/district.json';
        $url_subdistrict = 'https://raw.githubusercontent.com/kongvut/thai-province-data/refs/heads/master/api/latest/sub_district.json';

        $this->stdout("1) กำลังดาวน์โหลดข้อมูลจังหวัด (Provinces)...\n", Console::FG_CYAN);
        $json_p = @file_get_contents($url_province, false, $ctx);
        if (!$json_p) {
            $this->stdout("เกิดข้อผิดพลาดในการดาวน์โหลด province.json จาก GitHub\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
        $provinces = json_decode($json_p, true);
        $this->stdout("   ดาวน์โหลดสำเร็จ: " . count($provinces) . " จังหวัด\n", Console::FG_GREEN);

        $this->stdout("2) กำลังดาวน์โหลดข้อมูลอำเภอ/เขต (Districts/Amphurs)...\n", Console::FG_CYAN);
        $json_a = @file_get_contents($url_district, false, $ctx);
        if (!$json_a) {
            $this->stdout("เกิดข้อผิดพลาดในการดาวน์โหลด district.json จาก GitHub\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
        $amphurs = json_decode($json_a, true);
        $this->stdout("   ดาวน์โหลดสำเร็จ: " . count($amphurs) . " อำเภอ/เขต\n", Console::FG_GREEN);

        $this->stdout("3) กำลังดาวน์โหลดข้อมูลตำบล/แขวง และรหัสไปรษณีย์ (Subdistricts)...\n", Console::FG_CYAN);
        $json_s = @file_get_contents($url_subdistrict, false, $ctx);
        if (!$json_s) {
            $this->stdout("เกิดข้อผิดพลาดในการดาวน์โหลด sub_district.json จาก GitHub\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
        $subdistricts = json_decode($json_s, true);
        $this->stdout("   ดาวน์โหลดสำเร็จ: " . count($subdistricts) . " ตำบล/แขวง\n", Console::FG_GREEN);

        // 4. บันทึก/อัปเดต จังหวัด
        $this->stdout("\n=== เริ่มกระบวนการ Sync ข้อมูล ===\n", Console::FG_YELLOW);
        $province_count = 0;
        foreach ($provinces as $p) {
            $p_id = (int)$p['id'];
            $p_code = (int)$p['code'];
            $p_name_th = trim($p['name_th']);
            $geo_id = 1;

            $sql = "INSERT INTO `province` (`PROVINCE_ID`, `PROVINCE_CODE`, `PROVINCE_NAME`, `GEO_ID`) 
                    VALUES (:id, :code, :name, :geo) 
                    ON DUPLICATE KEY UPDATE 
                    `PROVINCE_NAME` = VALUES(`PROVINCE_NAME`), 
                    `PROVINCE_CODE` = VALUES(`PROVINCE_CODE`)";
            $db->createCommand($sql, [
                ':id' => $p_id,
                ':code' => $p_code,
                ':name' => $p_name_th,
                ':geo' => $geo_id
            ])->execute();
            $province_count++;
        }
        $this->stdout("อัปเดตจังหวัดเรียบร้อย: $province_count รายการ\n", Console::FG_GREEN);

        // 5. บันทึก/อัปเดต อำเภอ
        $amphur_count = 0;
        foreach ($amphurs as $a) {
            $a_id = (int)$a['id'];
            $a_code = (int)$a['code'];
            $a_name_th = trim($a['name_th']);
            $p_id = (int)$a['province_id'];

            $sql = "INSERT INTO `amphur` (`AMPHUR_ID`, `AMPHUR_CODE`, `AMPHUR_NAME`, `GEO_ID`, `PROVINCE_ID`) 
                    VALUES (:id, :code, :name, 1, :province_id) 
                    ON DUPLICATE KEY UPDATE 
                    `AMPHUR_NAME` = VALUES(`AMPHUR_NAME`), 
                    `AMPHUR_CODE` = VALUES(`AMPHUR_CODE`), 
                    `PROVINCE_ID` = VALUES(`PROVINCE_ID`)";
            $db->createCommand($sql, [
                ':id' => $a_id,
                ':code' => $a_code,
                ':name' => $a_name_th,
                ':province_id' => $p_id
            ])->execute();
            $amphur_count++;
        }
        $this->stdout("อัปเดตอำเภอเรียบร้อย: $amphur_count รายการ\n", Console::FG_GREEN);

        // 6. บันทึก/อัปเดต ตำบลและรหัสไปรษณีย์
        $district_count = 0;
        $batch = [];
        $total_sub = count($subdistricts);

        for ($idx = 0; $idx < $total_sub; $idx++) {
            $s = $subdistricts[$idx];
            $d_id = (int)$s['id'];
            $d_code = (int)$s['code'];
            $d_name = addslashes(trim($s['name_th']));
            $zipcode = !empty($s['zip_code']) ? sprintf('%05d', $s['zip_code']) : '';
            $a_id = (int)$s['district_id'];
            $p_id = (int)$s['province_id'];

            $batch[] = "($d_id, '$d_code', '$d_name', '$zipcode', $a_id, $p_id)";

            if (count($batch) >= 500 || $idx == $total_sub - 1) {
                $sql = "INSERT INTO `district` (`DISTRICT_ID`, `DISTRICT_CODE`, `DISTRICT_NAME`, `ZIPCODE`, `AMPHUR_ID`, `PROVINCE_ID`) 
                        VALUES " . implode(',', $batch) . " 
                        ON DUPLICATE KEY UPDATE 
                        `DISTRICT_NAME` = VALUES(`DISTRICT_NAME`),
                        `ZIPCODE` = VALUES(`ZIPCODE`),
                        `AMPHUR_ID` = VALUES(`AMPHUR_ID`),
                        `PROVINCE_ID` = VALUES(`PROVINCE_ID`)";
                $db->createCommand($sql)->execute();
                $district_count += count($batch);
                $batch = [];
            }
        }
        $this->stdout("อัปเดตตำบล/แขวงเรียบร้อย: $district_count รายการ\n", Console::FG_GREEN);

        // 7. อัปเดต POSTCODE ใน amphur
        $db->createCommand("
            UPDATE `amphur` a 
            JOIN (
                SELECT `AMPHUR_ID`, MIN(`ZIPCODE`) as min_zip 
                FROM `district` 
                WHERE `ZIPCODE` IS NOT NULL AND `ZIPCODE` != '' 
                GROUP BY `AMPHUR_ID`
            ) d ON a.`AMPHUR_ID` = d.`AMPHUR_ID`
            SET a.`POSTCODE` = d.min_zip
        ")->execute();
        $this->stdout("อัปเดตรหัสไปรษณีย์ระดับอำเภอเรียบร้อย\n", Console::FG_GREEN);

        // สรุปข้อมูลสุดท้าย
        $this->stdout("\n=== สรุปผลการอัปเดต ===\n", Console::FG_YELLOW);
        $p_total = $db->createCommand("SELECT COUNT(*) FROM `province`")->queryScalar();
        $a_total = $db->createCommand("SELECT COUNT(*) FROM `amphur`")->queryScalar();
        $d_total = $db->createCommand("SELECT COUNT(*) FROM `district`")->queryScalar();
        $z_total = $db->createCommand("SELECT COUNT(*) FROM `district` WHERE `ZIPCODE` IS NOT NULL AND `ZIPCODE` != ''")->queryScalar();

        $this->stdout("- จำนวนจังหวัดทั้งหมด: $p_total จังหวัด\n", Console::FG_CYAN);
        $this->stdout("- จำนวนอำเภอทั้งหมด: $a_total อำเภอ/เขต\n", Console::FG_CYAN);
        $this->stdout("- จำนวนตำบลทั้งหมด: $d_total ตำบล/แขวง\n", Console::FG_CYAN);
        $this->stdout("- จำนวนตำบลที่มีรหัสไปรษณีย์: $z_total รายการ\n", Console::FG_CYAN);
        $this->stdout("\n=== สำเร็จสมบูรณ์ ===\n", Console::FG_GREEN);

        return self::EXIT_CODE_NORMAL;
    }
}
