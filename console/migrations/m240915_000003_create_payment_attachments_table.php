<?php
/**
 * Migration สำหรับสร้างตาราง payment_attachments
 * ไฟล์: console/migrations/m240915_000003_create_payment_attachments_table.php
 */

use yii\db\Migration;

class m240915_000003_create_payment_attachments_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // สร้างตาราง payment_attachments
        $this->createTable('{{%payment_attachments}}', [
            'id' => $this->primaryKey(),
            'payment_receipt_id' => $this->integer()->notNull()->comment('รหัสใบเสร็จรับเงิน'),
            'file_name' => $this->string(255)->notNull()->comment('ชื่อไฟล์'),
            'original_name' => $this->string(255)->notNull()->comment('ชื่อไฟล์เดิม'),
            'file_path' => $this->string(500)->notNull()->comment('ที่อยู่ไฟล์'),
            'file_size' => $this->bigInteger()->notNull()->comment('ขนาดไฟล์ (bytes)'),
            'file_type' => $this->string(100)->notNull()->comment('ประเภทไฟล์'),
            'mime_type' => $this->string(100)->null()->comment('MIME Type'),
            'uploaded_by' => $this->integer()->notNull()->comment('ผู้อัพโหลด'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // สร้าง index
        $this->createIndex('idx-payment_attachments-payment_receipt_id', '{{%payment_attachments}}', 'payment_receipt_id');
        $this->createIndex('idx-payment_attachments-file_type', '{{%payment_attachments}}', 'file_type');
        $this->createIndex('idx-payment_attachments-uploaded_by', '{{%payment_attachments}}', 'uploaded_by');
        $this->createIndex('idx-payment_attachments-created_at', '{{%payment_attachments}}', 'created_at');

        // สร้าง foreign key constraints
        $this->addForeignKey(
            'fk-payment_attachments-payment_receipt_id',
            '{{%payment_attachments}}',
            'payment_receipt_id',
            '{{%payment_receipts}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Foreign key สำหรับ uploaded_by (ถ้าตาราง user มีอยู่)
        // $this->addForeignKey(
        //     'fk-payment_attachments-uploaded_by',
        //     '{{%payment_attachments}}',
        //     'uploaded_by',
        //     '{{%user}}',
        //     'id',
        //     'RESTRICT',
        //     'CASCADE'
        // );
    }

    public function safeDown()
    {
        // ลบ foreign keys
        $this->dropForeignKey('fk-payment_attachments-payment_receipt_id', '{{%payment_attachments}}');
        // $this->dropForeignKey('fk-payment_attachments-uploaded_by', '{{%payment_attachments}}');

        // ลบ indexes
        $this->dropIndex('idx-payment_attachments-payment_receipt_id', '{{%payment_attachments}}');
        $this->dropIndex('idx-payment_attachments-file_type', '{{%payment_attachments}}');
        $this->dropIndex('idx-payment_attachments-uploaded_by', '{{%payment_attachments}}');
        $this->dropIndex('idx-payment_attachments-created_at', '{{%payment_attachments}}');

        // ลบตาราง
        $this->dropTable('{{%payment_attachments}}');
    }
}