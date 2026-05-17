<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_report_doc}}`.
 */
class m260517_144455_create_job_report_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP TABLE IF EXISTS {{%job_report_doc}}');
        $this->createTable('{{%job_report_doc}}', [
            'id' => $this->primaryKey(),
            'job_id' => $this->integer()->notNull()->comment('รหัสใบงาน'),
            'folder_name' => $this->string(255)->comment('ชื่อโฟลเดอร์'),
            'file_name' => $this->string(255)->notNull()->comment('ชื่อไฟล์'),
            'file_path' => $this->string(255)->notNull()->comment('ที่อยู่ไฟล์'),
            'file_size' => $this->integer()->comment('ขนาดไฟล์ (bytes)'),
            'uploaded_at' => $this->integer()->comment('วันที่อัปโหลด'),
            'uploaded_by' => $this->integer()->comment('ผู้อัปโหลด'),
        ]);

        // สร้าง index
        $this->createIndex(
            'idx-job_report_doc-job_id',
            '{{%job_report_doc}}',
            'job_id'
        );

        // สร้าง foreign key
        $this->addForeignKey(
            'fk-job_report_doc-job_id',
            '{{%job_report_doc}}',
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
            'fk-job_report_doc-job_id',
            '{{%job_report_doc}}'
        );

        // ลบ index
        $this->dropIndex(
            'idx-job_report_doc-job_id',
            '{{%job_report_doc}}'
        );

        $this->dropTable('{{%job_report_doc}}');
    }
}
