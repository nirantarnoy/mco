<?php

use yii\db\Migration;

/**
 * Handles the creation of table `temp_invoice` and `temp_invoice_line`.
 */
class m260402_120919_create_temp_invoice_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Table for temporary invoice headers
        $this->createTable('{{%temp_invoice}}', [
            'id' => $this->primaryKey(),
            'invoice_type' => $this->string(50)->comment('ประเภทเอกสาร'),
            'invoice_number' => $this->string(100)->comment('เลขที่ใบแจ้งหนี้'),
            'invoice_date' => $this->date()->comment('วันที่ในเอกสาร'),
            'vendor_name' => $this->string(255)->comment('ชื่อผู้ขาย/ผู้ออกเอกสาร'),
            'customer_name' => $this->string(255)->comment('ชื่อลูกค้า'),
            'customer_tax_id' => $this->string(20)->comment('เลขประจำตัวผู้เสียภาษี'),
            'customer_address' => $this->text()->comment('ที่อยู่ลูกค้า'),
            'subtotal' => $this->decimal(18, 2)->defaultValue(0),
            'vat_amount' => $this->decimal(18, 2)->defaultValue(0),
            'total_amount' => $this->decimal(18, 2)->defaultValue(0),
            'raw_text' => $this->text()->comment('ข้อความดิบจากการ OCR'),
            'status' => $this->smallInteger()->defaultValue(0)->comment('0:รอยืนยัน, 1:ยืนยันแล้ว, 9:ยกเลิก'),
            'company_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
        ], $tableOptions);

        // Table for temporary invoice lines
        $this->createTable('{{%temp_invoice_line}}', [
            'id' => $this->primaryKey(),
            'temp_invoice_id' => $this->integer()->notNull(),
            'description' => $this->text()->comment('รายการ'),
            'quantity' => $this->decimal(18, 3)->defaultValue(1),
            'unit' => $this->string(50),
            'unit_price' => $this->decimal(18, 2)->defaultValue(0),
            'amount' => $this->decimal(18, 2)->defaultValue(0),
        ], $tableOptions);

        // Foreign Key
        $this->addForeignKey(
            'fk-temp_invoice_line-invoice_id',
            '{{%temp_invoice_line}}',
            'temp_invoice_id',
            '{{%temp_invoice}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-temp_invoice-company_id',
            '{{%temp_invoice}}',
            'company_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-temp_invoice_line-invoice_id', '{{%temp_invoice_line}}');
        $this->dropTable('{{%temp_invoice_line}}');
        $this->dropTable('{{%temp_invoice}}');
    }
}
