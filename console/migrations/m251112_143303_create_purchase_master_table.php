<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purchase_master}}`.
 */
class m251112_143303_create_purchase_master_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%purchase_master}}', [
            'id' => $this->primaryKey(),
            'docnum' => $this->string(50)->notNull()->unique()->comment('เลขที่เอกสาร'),
            'docdat' => $this->date()->notNull()->comment('วันที่เอกสาร'),
            'supcod' => $this->string(20)->comment('รหัสผู้จำหน่าย'),
            'supnam' => $this->string(255)->comment('ชื่อผู้จำหน่าย'),
            'job_no' => $this->string(50)->comment('JOB No.'),
            'paytrm' => $this->string(100)->comment('เครดิต'),
            'duedat' => $this->date()->comment('วันครบกำหนด'),
            'taxid' => $this->string(20)->comment('เลขประจำตัวผู้เสียภาษี'),
            'discod' => $this->string(50)->comment('ส่วนลด'),
            'addr01' => $this->string(255)->comment('ที่อยู่บรรทัด 1'),
            'addr02' => $this->string(255)->comment('ที่อยู่บรรทัด 2'),
            'addr03' => $this->string(255)->comment('ที่อยู่บรรทัด 3'),
            'zipcod' => $this->string(10)->comment('รหัสไปรษณีย์'),
            'telnum' => $this->string(50)->comment('เบอร์โทร'),
            'orgnum' => $this->string(50)->comment('สาขาเรา'),
            'refnum' => $this->string(50)->comment('เลขที่ใบกำกับ'),
            'vatdat' => $this->date()->comment('ภาษี'),
            'vatpr0' => $this->decimal(15, 2)->defaultValue(0)->comment('มูลค่าก่อนภาษี'),
            'amount' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงิน'),
            'unitpr' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคาต่อหน่วย'),
            'disc' => $this->string(50)->comment('ส่วนลดเพิ่มเติม'),
            'vat_percent' => $this->decimal(5, 2)->defaultValue(7)->comment('VAT %'),
            'vat_amount' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวน VAT'),
            'tax_percent' => $this->decimal(5, 2)->defaultValue(0)->comment('TAX %'),
            'tax_amount' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวน TAX'),
            'total_amount' => $this->decimal(15, 2)->defaultValue(0)->comment('รวมสุทธิ'),
            'remark' => $this->text()->comment('หมายเหตุ'),
            'status' => $this->integer()->defaultValue(1)->comment('สถานะ 1=ปกติ, 0=ยกเลิก'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // สร้าง index
        $this->createIndex(
            'idx-purchase_master-docnum',
            '{{%purchase_master}}',
            'docnum'
        );

        $this->createIndex(
            'idx-purchase_master-docdat',
            '{{%purchase_master}}',
            'docdat'
        );

        $this->createIndex(
            'idx-purchase_master-supcod',
            '{{%purchase_master}}',
            'supcod'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purchase_master}}');
    }
}
