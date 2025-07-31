<?php

use yii\db\Migration;

/**
 * Migration for Petty Cash Voucher System
 */
class m241231_120000_create_petty_cash_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create petty_cash_voucher table
        $this->createTable('{{%petty_cash_voucher}}', [
            'id' => $this->primaryKey(),
            'pcv_no' => $this->string(50)->notNull()->unique()->comment('PCV Number'),
            'date' => $this->date()->notNull()->comment('วันที่'),
            'name' => $this->string(255)->notNull()->comment('ชื่อผู้รับเงิน'),
            'amount' => $this->decimal(10, 2)->notNull()->comment('จำนวนเงินรวม'),
            'paid_for' => $this->text()->comment('จ่ายเพื่อ'),
            'issued_by' => $this->string(255)->comment('ผู้จัดทำ'),
            'issued_date' => $this->date()->comment('วันที่จัดทำ'),
            'approved_by' => $this->string(255)->comment('ผู้อนุมัติ'),
            'approved_date' => $this->date()->comment('วันที่อนุมัติ'),
            'status' => $this->tinyInteger(1)->defaultValue(1)->comment('1=Active, 0=Inactive'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        // Create petty_cash_detail table
        $this->createTable('{{%petty_cash_detail}}', [
            'id' => $this->primaryKey(),
            'voucher_id' => $this->integer()->notNull()->comment('Foreign key to petty_cash_voucher'),
            'ac_code' => $this->string(50)->comment('รหัสบัญชี'),
            'detail_date' => $this->date()->comment('วันที่รายการ'),
            'detail' => $this->text()->comment('รายละเอียด'),
            'amount' => $this->decimal(10, 2)->notNull()->defaultValue(0.00)->comment('จำนวนเงิน'),
            'vat' => $this->decimal(10, 2)->defaultValue(0.00)->comment('ภาษีมูลค่าเพิ่ม'),
            'vat_amount' => $this->decimal(10, 2)->defaultValue(0.00)->comment('จำนวนภาษี'),
            'wht' => $this->decimal(10, 2)->defaultValue(0.00)->comment('หักภาษี ณ ที่จ่าย'),
            'other' => $this->decimal(10, 2)->defaultValue(0.00)->comment('อื่นๆ'),
            'total' => $this->decimal(10, 2)->notNull()->defaultValue(0.00)->comment('รวม'),
            'sort_order' => $this->integer()->defaultValue(0)->comment('ลำดับการแสดงผล'),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        // Create petty_cash_sequence table
        $this->createTable('{{%petty_cash_sequence}}', [
            'year' => $this->integer(4)->notNull(),
            'last_number' => $this->integer()->notNull()->defaultValue(0),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        // Add primary key for sequence table
        $this->addPrimaryKey('pk_petty_cash_sequence', '{{%petty_cash_sequence}}', 'year');

        // Add foreign key constraint
        $this->addForeignKey(
            'fk_petty_cash_detail_voucher',
            '{{%petty_cash_detail}}',
            'voucher_id',
            '{{%petty_cash_voucher}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes
        $this->createIndex('idx_petty_cash_voucher_pcv_no', '{{%petty_cash_voucher}}', 'pcv_no');
        $this->createIndex('idx_petty_cash_voucher_date', '{{%petty_cash_voucher}}', 'date');
        $this->createIndex('idx_petty_cash_voucher_status', '{{%petty_cash_voucher}}', 'status');
        $this->createIndex('idx_petty_cash_detail_voucher_id', '{{%petty_cash_detail}}', 'voucher_id');
        $this->createIndex('idx_petty_cash_detail_sort_order', '{{%petty_cash_detail}}', 'sort_order');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk_petty_cash_detail_voucher', '{{%petty_cash_detail}}');

        // Drop tables
        $this->dropTable('{{%petty_cash_detail}}');
        $this->dropTable('{{%petty_cash_sequence}}');
        $this->dropTable('{{%petty_cash_voucher}}');
    }
}