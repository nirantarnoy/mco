<?php
/**
 * Migration สำหรับสร้างตาราง payment_receipt_details
 * ไฟล์: console/migrations/m240915_000002_create_payment_receipt_details_table.php
 */

use yii\db\Migration;

class m240915_000002_create_payment_receipt_details_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // สร้างตาราง payment_receipt_details
        $this->createTable('{{%payment_receipt_details}}', [
            'id' => $this->primaryKey(),
            'payment_receipt_id' => $this->integer()->notNull()->comment('รหัสใบเสร็จรับเงิน'),
            'billing_invoice_item_id' => $this->integer()->null()->comment('รหัสรายการใบแจ้งหนี้'),
            'description' => $this->string(500)->notNull()->comment('รายละเอียด'),
            'amount' => $this->decimal(15, 2)->notNull()->comment('จำนวนเงิน'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // สร้าง index
        $this->createIndex('idx-payment_receipt_details-payment_receipt_id', '{{%payment_receipt_details}}', 'payment_receipt_id');
        $this->createIndex('idx-payment_receipt_details-billing_invoice_item_id', '{{%payment_receipt_details}}', 'billing_invoice_item_id');

        // สร้าง foreign key constraints
        $this->addForeignKey(
            'fk-payment_receipt_details-payment_receipt_id',
            '{{%payment_receipt_details}}',
            'payment_receipt_id',
            '{{%payment_receipts}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Foreign key สำหรับ billing_invoice_item_id (ถ้าตารางมีอยู่)
        // $this->addForeignKey(
        //     'fk-payment_receipt_details-billing_invoice_item_id',
        //     '{{%payment_receipt_details}}',
        //     'billing_invoice_item_id',
        //     '{{%billing_invoice_items}}',
        //     'id',
        //     'SET NULL',
        //     'CASCADE'
        // );
    }

    public function safeDown()
    {
        // ลบ foreign keys
        $this->dropForeignKey('fk-payment_receipt_details-payment_receipt_id', '{{%payment_receipt_details}}');
        // $this->dropForeignKey('fk-payment_receipt_details-billing_invoice_item_id', '{{%payment_receipt_details}}');

        // ลบ indexes
        $this->dropIndex('idx-payment_receipt_details-payment_receipt_id', '{{%payment_receipt_details}}');
        $this->dropIndex('idx-payment_receipt_details-billing_invoice_item_id', '{{%payment_receipt_details}}');

        // ลบตาราง
        $this->dropTable('{{%payment_receipt_details}}');
    }
}
