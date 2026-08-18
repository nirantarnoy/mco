<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use backend\controllers\VehicleExpenseController as BackendVehicleExpenseController;

/**
 * VehicleExpenseController handles automated Google Sheets data sync via CLI/Cron.
 * Command: php yii vehicle-expense/sync-daily [date] [--all]
 */
class VehicleExpenseController extends Controller
{
    /**
     * ดึงข้อมูลค่าใช้จ่ายรถยนต์จาก Google Sheets เข้าสู่ระบบประจำวัน
     * 
     * ตัวอย่างการใช้งาน:
     *   php yii vehicle-expense/sync-daily              (ดึงข้อมูลวันปัจจุบัน)
     *   php yii vehicle-expense/sync-daily 2026-08-18   (ดึงข้อมูลประจำวันที่ระบุ)
     *   php yii vehicle-expense/sync-daily all          (ดึงข้อมูลย้อนหลังทั้งหมด)
     */
    public function actionSyncDaily($date = null)
    {
        $syncAll = false;
        $targetDate = null;

        if ($date === 'all') {
            $syncAll = true;
        } elseif (!empty($date)) {
            $targetDate = date('Y-m-d', strtotime($date));
        } else {
            $targetDate = date('Y-m-d');
        }

        $displayDate = $syncAll ? 'ทั้งหมด' : $targetDate;
        $this->stdout("กำลังดึงข้อมูลค่าใช้จ่ายรถยนต์จาก Google Sheets (วันที่: {$displayDate})...\n", Console::FG_YELLOW);

        try {
            $backendController = new BackendVehicleExpenseController('vehicle-expense', $this->module);
            $result = $backendController->syncGoogleSheetData($syncAll ? null : $targetDate, $syncAll);

            $this->stdout("ดึงข้อมูลสำเร็จ!\n", Console::FG_GREEN);
            $this->stdout("- นำเข้าใหม่: {$result['success']} รายการ\n", Console::FG_CYAN);
            $this->stdout("- ซ้ำ (มีอยู่แล้ว): {$result['duplicate']} รายการ\n", Console::FG_GREY);
            $this->stdout("- ข้าม: {$result['skipped']} รายการ\n", Console::FG_GREY);

            if (!empty($result['errors'])) {
                $this->stdout("- ข้อผิดพลาด: {$result['errors']} รายการ\n", Console::FG_RED);
            }

            return Controller::EXIT_CODE_NORMAL;
        } catch (\Exception $e) {
            $this->stderr("เกิดข้อผิดพลาด: " . $e->getMessage() . "\n", Console::FG_RED);
            Yii::error("Console Sync Exception: " . $e->getMessage(), __METHOD__);
            return Controller::EXIT_CODE_ERROR;
        }
    }
}
