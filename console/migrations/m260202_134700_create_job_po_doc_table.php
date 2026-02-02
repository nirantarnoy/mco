<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_po_doc}}`.
 */
class m260202_134700_create_job_po_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_po_doc}}', [
            'id' => $this->primaryKey(),
            'job_id' => $this->integer()->notNull()->comment('รหัสใบงาน'),
            'file_name' => $this->string(255)->notNull()->comment('ชื่อไฟล์'),
            'file_path' => $this->string(255)->notNull()->comment('ที่อยู่ไฟล์'),
            'file_size' => $this->integer()->comment('ขนาดไฟล์ (bytes)'),
            'uploaded_at' => $this->integer()->comment('วันที่อัปโหลด'),
            'uploaded_by' => $this->integer()->comment('ผู้อัปโหลด'),
        ]);

        // สร้าง index
        $this->createIndex(
            'idx-job_po_doc-job_id',
            '{{%job_po_doc}}',
            'job_id'
        );

        // สร้าง foreign key
        $this->addForeignKey(
            'fk-job_po_doc-job_id',
            '{{%job_po_doc}}',
            'job_id',
            '{{%job}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // ลบ foreign key
        $this->dropForeignKey(
            'fk-job_po_doc-job_id',
            '{{%job_po_doc}}'
        );

        // ลบ index
        $this->dropIndex(
            'idx-job_po_doc-job_id',
            '{{%job_po_doc}}'
        );

        $this->dropTable('{{%job_po_doc}}');
    }
}
