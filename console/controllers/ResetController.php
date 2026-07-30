<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class ResetController extends Controller
{
    /**
     * ลบข้อมูล Payment Voucher ทั้งหมดและรีเซ็ตสถานะ
     */
    public function actionPaymentVouchers()
    {
        $this->stdout("Starting to delete all Payment Vouchers...\n", Console::FG_YELLOW);

        if (!$this->confirm("Are you sure you want to delete ALL Payment Vouchers? This action cannot be undone.")) {
            $this->stdout("Operation cancelled by user.\n", Console::FG_RED);
            return self::EXIT_CODE_NORMAL;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // ปิดการตรวจสอบ Foreign Key ชั่วคราว
            Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();

            $this->stdout("Truncating payment_voucher_doc...\n", Console::FG_CYAN);
            Yii::$app->db->createCommand('TRUNCATE TABLE payment_voucher_doc;')->execute();

            $this->stdout("Truncating payment_voucher_ref...\n", Console::FG_CYAN);
            Yii::$app->db->createCommand('TRUNCATE TABLE payment_voucher_ref;')->execute();

            $this->stdout("Truncating payment_voucher_line...\n", Console::FG_CYAN);
            Yii::$app->db->createCommand('TRUNCATE TABLE payment_voucher_line;')->execute();

            $this->stdout("Truncating payment_voucher...\n", Console::FG_CYAN);
            Yii::$app->db->createCommand('TRUNCATE TABLE payment_voucher;')->execute();

            // เปิดการตรวจสอบ Foreign Key คืน
            Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();

            $transaction->commit();
            $this->stdout("Successfully deleted all Payment Vouchers and related data.\n", Console::FG_GREEN);
            $this->stdout("Statuses for PR, PO, Pre-Advance, and None PR have automatically reverted to normal.\n", Console::FG_GREEN);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute(); // make sure it's re-enabled
            $this->stdout("Error occurred: " . $e->getMessage() . "\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }

        return self::EXIT_CODE_NORMAL;
    }
}
