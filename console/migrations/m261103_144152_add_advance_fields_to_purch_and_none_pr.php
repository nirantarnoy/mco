<?php

use yii\db\Migration;

class m261103_144152_add_advance_fields_to_purch_and_none_pr extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. ตาราง purch (PO)
        $tablePurch = $this->db->getTableSchema('purch');
        if ($tablePurch) {
            if (!isset($tablePurch->columns['is_advance'])) {
                $this->addColumn('purch', 'is_advance', $this->smallInteger(1)->defaultValue(0)->comment('0=ปกติ, 1=โอนมัดจำล่วงหน้าก่อนรับของ'));
            }
            if (!isset($tablePurch->columns['payment_due_date'])) {
                $this->addColumn('purch', 'payment_due_date', $this->date()->null()->comment('วันที่นัดชำระเงิน/โอนมัดจำ'));
            }
            if (!isset($tablePurch->columns['invoice_no'])) {
                $this->addColumn('purch', 'invoice_no', $this->string(100)->null()->comment('เลขที่ใบแจ้งหนี้/ใบวางบิล'));
            }
        }

        // 2. ตาราง purchase_master (None PR)
        $tableNpr = $this->db->getTableSchema('purchase_master');
        if ($tableNpr) {
            if (!isset($tableNpr->columns['is_advance'])) {
                $this->addColumn('purchase_master', 'is_advance', $this->smallInteger(1)->defaultValue(0)->comment('0=ปกติ/รับของแล้ว, 1=โอนมัดจำล่วงหน้ายังไม่รับของ'));
            }
            if (!isset($tableNpr->columns['payment_due_date'])) {
                $this->addColumn('purchase_master', 'payment_due_date', $this->date()->null()->comment('วันที่นัดชำระเงิน/โอนมัดจำ'));
            }
            if (!isset($tableNpr->columns['invoice_no'])) {
                $this->addColumn('purchase_master', 'invoice_no', $this->string(100)->null()->comment('เลขที่ใบแจ้งหนี้/ใบวางบิล'));
            }
        }
    }

    public function safeDown()
    {
        $tablePurch = $this->db->getTableSchema('purch');
        if ($tablePurch) {
            if (isset($tablePurch->columns['is_advance'])) {
                $this->dropColumn('purch', 'is_advance');
            }
            if (isset($tablePurch->columns['payment_due_date'])) {
                $this->dropColumn('purch', 'payment_due_date');
            }
            if (isset($tablePurch->columns['invoice_no'])) {
                $this->dropColumn('purch', 'invoice_no');
            }
        }

        $tableNpr = $this->db->getTableSchema('purchase_master');
        if ($tableNpr) {
            if (isset($tableNpr->columns['is_advance'])) {
                $this->dropColumn('purchase_master', 'is_advance');
            }
            if (isset($tableNpr->columns['payment_due_date'])) {
                $this->dropColumn('purchase_master', 'payment_due_date');
            }
            if (isset($tableNpr->columns['invoice_no'])) {
                $this->dropColumn('purchase_master', 'invoice_no');
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261103_144152_add_advance_fields_to_purch_and_none_pr cannot be reverted.\n";

        return false;
    }
    */
}
