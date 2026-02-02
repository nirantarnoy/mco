<?php

use yii\db\Migration;

/**
 * สร้างตารางเชื่อมโยง Payment Voucher กับ PR/PO หลายรายการ
 */
class m260202_141700_create_payment_voucher_ref_table extends Migration
{
    public function safeUp()
    {
        // สร้างตาราง payment_voucher_ref
        $this->createTable('{{%payment_voucher_ref}}', [
            'id' => $this->primaryKey(),
            'payment_voucher_id' => $this->integer()->notNull()->comment('รหัส Payment Voucher'),
            'ref_type' => $this->integer()->notNull()->comment('ประเภท: 1=PR, 2=PO'),
            'ref_id' => $this->integer()->notNull()->comment('รหัสอ้างอิง PR/PO'),
            'ref_no' => $this->string(100)->comment('เลขที่เอกสารอ้างอิง'),
            'amount' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินที่จ่าย'),
            'created_at' => $this->integer()->comment('วันที่สร้าง'),
        ]);

        // สร้าง index
        $this->createIndex(
            'idx-payment_voucher_ref-payment_voucher_id',
            '{{%payment_voucher_ref}}',
            'payment_voucher_id'
        );

        $this->createIndex(
            'idx-payment_voucher_ref-ref_type_id',
            '{{%payment_voucher_ref}}',
            ['ref_type', 'ref_id']
        );

        // สร้าง foreign key
        $this->addForeignKey(
            'fk-payment_voucher_ref-payment_voucher_id',
            '{{%payment_voucher_ref}}',
            'payment_voucher_id',
            '{{%payment_voucher}}',
            'id',
            'CASCADE'
        );

        // เพิ่มฟิลด์ vendor_id ในตาราง payment_voucher
        $this->addColumn('{{%payment_voucher}}', 'vendor_id', $this->integer()->after('recipient_name')->comment('รหัส Vendor'));
    }

    public function safeDown()
    {
        // ลบฟิลด์ vendor_id
        $this->dropColumn('{{%payment_voucher}}', 'vendor_id');

        // ลบ foreign key
        $this->dropForeignKey(
            'fk-payment_voucher_ref-payment_voucher_id',
            '{{%payment_voucher_ref}}'
        );

        // ลบ index
        $this->dropIndex(
            'idx-payment_voucher_ref-ref_type_id',
            '{{%payment_voucher_ref}}'
        );

        $this->dropIndex(
            'idx-payment_voucher_ref-payment_voucher_id',
            '{{%payment_voucher_ref}}'
        );

        $this->dropTable('{{%payment_voucher_ref}}');
    }
}
