<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vehicle_expense}}`.
 */
class m250105_000000_create_vehicle_expense_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%vehicle_expense}}', [
            'id' => $this->primaryKey()->comment('รหัสอ้างอิง'),
            'expense_date' => $this->date()->notNull()->comment('วันที่ใช้จ่าย'),
            'job_no' => $this->string(50)->null()->comment('เลขที่ใบงาน'),
            'job_description' => $this->text()->null()->comment('รายละเอียดงาน'),
            'vehicle_no' => $this->string(20)->null()->comment('หมายเลขรถ'),
            'driver_name' => $this->string(100)->null()->comment('ชื่อพนักงานขับรถ'),
            'distance_start' => $this->decimal(10, 2)->null()->defaultValue(0)->comment('ระยะทางเริ่มต้น (กม.)'),
            'distance_end' => $this->decimal(10, 2)->null()->defaultValue(0)->comment('ระยะทางสิ้นสุด (กม.)'),
            'quantity' => $this->integer()->null()->defaultValue(1)->comment('จำนวน'),
            'amount' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('ค่าใช้จ่าย (บาท)'),
            'is_summary' => $this->tinyInteger(1)->defaultValue(0)->comment('เป็นแถวรวม 0=ไม่ใช่, 1=ใช่'),
            'import_batch' => $this->string(50)->null()->comment('รหัส Batch การ Import'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('วันที่แก้ไข'),
        ], $tableOptions);

        // สร้าง Index
        $this->createIndex(
            'idx_job_no',
            '{{%vehicle_expense}}',
            'job_no'
        );

        $this->createIndex(
            'idx_expense_date',
            '{{%vehicle_expense}}',
            'expense_date'
        );

        $this->createIndex(
            'idx_vehicle_no',
            '{{%vehicle_expense}}',
            'vehicle_no'
        );

        $this->createIndex(
            'idx_import_batch',
            '{{%vehicle_expense}}',
            'import_batch'
        );

        // Comment สำหรับตาราง
        $this->addCommentOnTable('{{%vehicle_expense}}', 'ตารางบันทึกค่าใช้จ่ายรถยนต์');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vehicle_expense}}');
    }
}