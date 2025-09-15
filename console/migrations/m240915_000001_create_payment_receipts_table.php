<?php
/**
* Migration สำหรับสร้างตาราง payment_receipts
* ไฟล์: console/migrations/m240915_000001_create_payment_receipts_table.php
*/

use yii\db\Migration;

class m240915_000001_create_payment_receipts_table extends Migration
{
public function safeUp()
{
$tableOptions = null;
if ($this->db->driverName === 'mysql') {
$tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
}

// สร้างตาราง payment_receipts
$this->createTable('{{%payment_receipts}}', [
'id' => $this->primaryKey(),
'receipt_number' => $this->string(50)->notNull()->unique()->comment('เลขที่ใบเสร็จ'),
'billing_invoice_id' => $this->integer()->notNull()->comment('รหัสใบแจ้งหนี้'),
'job_id' => $this->integer()->null()->comment('รหัสใบงาน'),
'payment_date' => $this->date()->notNull()->comment('วันที่รับเงิน'),
'payment_method' => "ENUM('cash','bank_transfer','cheque','credit_card','other') NOT NULL DEFAULT 'cash' COMMENT 'วิธีการชำระ'",
'bank_name' => $this->string(100)->null()->comment('ชื่อธนาคาร'),
'account_number' => $this->string(50)->null()->comment('เลขที่บัญชี'),
'cheque_number' => $this->string(50)->null()->comment('เลขที่เช็ค'),
'cheque_date' => $this->date()->null()->comment('วันที่เช็ค'),
'received_amount' => $this->decimal(15, 2)->notNull()->comment('จำนวนเงินที่รับ'),
'discount_amount' => $this->decimal(15, 2)->defaultValue(0.00)->comment('ส่วนลด'),
'vat_amount' => $this->decimal(15, 2)->defaultValue(0.00)->comment('ภาษีมูลค่าเพิ่ม'),
'withholding_tax' => $this->decimal(15, 2)->defaultValue(0.00)->comment('ภาษีหัก ณ ที่จ่าย'),
'net_amount' => $this->decimal(15, 2)->notNull()->comment('จำนวนเงินสุทธิ'),
'remaining_balance' => $this->decimal(15, 2)->defaultValue(0.00)->comment('ยอดคงเหลือ'),
'payment_status' => "ENUM('partial','full','overpaid') NOT NULL DEFAULT 'partial' COMMENT 'สถานะการชำระ'",
'attachment_path' => $this->string(500)->null()->comment('ที่อยู่ไฟล์แนบ'),
'attachment_name' => $this->string(255)->null()->comment('ชื่อไฟล์แนบ'),
'notes' => $this->text()->null()->comment('หมายเหตุ'),
'received_by' => $this->integer()->notNull()->comment('ผู้รับเงิน'),
'created_by' => $this->integer()->notNull(),
'updated_by' => $this->integer()->null(),
'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
], $tableOptions);

// สร้าง index
$this->createIndex('idx-payment_receipts-billing_invoice_id', '{{%payment_receipts}}', 'billing_invoice_id');
$this->createIndex('idx-payment_receipts-job_id', '{{%payment_receipts}}', 'job_id');
$this->createIndex('idx-payment_receipts-payment_date', '{{%payment_receipts}}', 'payment_date');
$this->createIndex('idx-payment_receipts-payment_status', '{{%payment_receipts}}', 'payment_status');
$this->createIndex('idx-payment_receipts-payment_method', '{{%payment_receipts}}', 'payment_method');
$this->createIndex('idx-payment_receipts-received_by', '{{%payment_receipts}}', 'received_by');
$this->createIndex('idx-payment_receipts-created_by', '{{%payment_receipts}}', 'created_by');
$this->createIndex('idx-payment_receipts-updated_by', '{{%payment_receipts}}', 'updated_by');
$this->createIndex('idx-payment_receipts-status', '{{%payment_receipts}}', 'status');

// สร้าง foreign key constraints (ถ้าตารางอ้างอิงมีอยู่)
// *** หมายเหตุ: ปรับชื่อตารางตามโครงสร้างจริงของระบบ ***

// Foreign key สำหรับ billing_invoice_id
// $this->addForeignKey(
//     'fk-payment_receipts-billing_invoice_id',
//     '{{%payment_receipts}}',
//     'billing_invoice_id',
//     '{{%billing_invoices}}',
//     'id',
//     'CASCADE',
//     'CASCADE'
// );

// Foreign key สำหรับ job_id
// $this->addForeignKey(
//     'fk-payment_receipts-job_id',
//     '{{%payment_receipts}}',
//     'job_id',
//     '{{%jobs}}',
//     'id',
//     'SET NULL',
//     'CASCADE'
// );

// Foreign key สำหรับ user fields
// $this->addForeignKey(
//     'fk-payment_receipts-received_by',
//     '{{%payment_receipts}}',
//     'received_by',
//     '{{%user}}',
//     'id',
//     'RESTRICT',
//     'CASCADE'
// );

// $this->addForeignKey(
//     'fk-payment_receipts-created_by',
//     '{{%payment_receipts}}',
//     'created_by',
//     '{{%user}}',
//     'id',
//     'RESTRICT',
//     'CASCADE'
// );

// $this->addForeignKey(
//     'fk-payment_receipts-updated_by',
//     '{{%payment_receipts}}',
//     'updated_by',
//     '{{%user}}',
//     'id',
//     'RESTRICT',
//     'CASCADE'
// );
}

public function safeDown()
{
// ลบ foreign keys ก่อน (ถ้ามี)
// $this->dropForeignKey('fk-payment_receipts-billing_invoice_id', '{{%payment_receipts}}');
// $this->dropForeignKey('fk-payment_receipts-job_id', '{{%payment_receipts}}');
// $this->dropForeignKey('fk-payment_receipts-received_by', '{{%payment_receipts}}');
// $this->dropForeignKey('fk-payment_receipts-created_by', '{{%payment_receipts}}');
// $this->dropForeignKey('fk-payment_receipts-updated_by', '{{%payment_receipts}}');

// ลบ indexes
$this->dropIndex('idx-payment_receipts-billing_invoice_id', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-job_id', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-payment_date', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-payment_status', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-payment_method', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-received_by', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-created_by', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-updated_by', '{{%payment_receipts}}');
$this->dropIndex('idx-payment_receipts-status', '{{%payment_receipts}}');

// ลบตาราง
$this->dropTable('{{%payment_receipts}}');
}
}