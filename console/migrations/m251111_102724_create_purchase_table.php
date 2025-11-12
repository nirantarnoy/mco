<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purchase}}`.
 */
class m251111_102724_create_purchase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purchase}}', [
            'id' => $this->primaryKey(),
            'dedcod' => $this->string(255)->comment('รหัสแผนก'),
            'docnum' => $this->string(255)->comment('เลขที่เอกสาร'),
            'docdat' => $this->date()->comment('วันที่เอกสาร'),
            'supcod' => $this->string(255)->comment('รหัสผู้จำหน่าย'),
            'supnam' => $this->string(255)->comment('ชื่อผู้จำหน่าย'),
            'stkcod' => $this->string(255)->comment('รหัสสินค้า'),
            'stkdes' => $this->string(255)->comment('รายละเอียดสินค้า'),
            'trnqty' => $this->decimal(15, 2)->comment('จำนวน'),
            'untpri' => $this->string(255)->comment('ราคาต่อหน่วย'),
            'disc' => $this->decimal(15, 2)->comment('ส่วนลด'),
            'amount' => $this->decimal(15, 2)->comment('จำนวนเงิน'),
            'payfrm' => $this->string(255)->comment('วิธีชำระ'),
            'duedat' => $this->date()->comment('วันครบกำหนด'),
            'taxid' => $this->string(255)->comment('เลขประจำตัวผู้เสียภาษี'),
            'discod' => $this->string(255)->comment('ส่วนลดทั่วไป'),
            'addr01' => $this->decimal(15, 2)->comment('ที่อยู่ 1'),
            'addr02' => $this->decimal(15, 2)->comment('ที่อยู่ 2'),
            'addr03' => $this->decimal(15, 2)->comment('ที่อยู่ 3'),
            'zipcod' => $this->string(255)->comment('รหัสไปรษณีย์'),
            'telnum' => $this->string(255)->comment('เบอร์โทร'),
            'orgnum' => $this->string(255)->comment('ลำดับเรียง'),
            'refnum' => $this->string(255)->comment('เลขที่ใบกำกับ'),
            'vatdat' => $this->date()->comment('วันที่ภาษี'),
            'vatpr0' => $this->decimal(15, 2)->comment('มูลค่าภาษี'),
            'late' => $this->string(255)->comment('ยังไม่ได้เอกสาร'),
            'created_at' => $this->dateTime()->comment('สร้างเมื่อ'),
            'created_by' => $this->integer()->comment('สร้างโดย'),
            'updated_at' => $this->dateTime()->comment('แก้ไขเมื่อ'),
            'updated_by' => $this->integer()->comment('แก้ไขโดย'),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB COMMENT="ตารางบันทึกข้อมูลซื้อ"');

        // สร้าง index สำหรับฟิลด์ที่ใช้ค้นหาบ่อย
        $this->createIndex(
            'idx-purchase-docnum',
            '{{%purchase}}',
            'docnum'
        );

        $this->createIndex(
            'idx-purchase-supcod',
            '{{%purchase}}',
            'supcod'
        );

        $this->createIndex(
            'idx-purchase-docdat',
            '{{%purchase}}',
            'docdat'
        );

        $this->createIndex(
            'idx-purchase-stkcod',
            '{{%purchase}}',
            'stkcod'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // ลบ index ก่อน
        $this->dropIndex('idx-purchase-docnum', '{{%purchase}}');
        $this->dropIndex('idx-purchase-supcod', '{{%purchase}}');
        $this->dropIndex('idx-purchase-docdat', '{{%purchase}}');
        $this->dropIndex('idx-purchase-stkcod', '{{%purchase}}');

        // ลบตาราง
        $this->dropTable('{{%purchase}}');
    }
}
