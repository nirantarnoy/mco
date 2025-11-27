<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%receiving_checklist}}`.
 */
class m251126_164202_create_receiving_checklist_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%receiving_checklist}}', [
            'id' => $this->primaryKey(),
            'purch_id' => $this->integer()->notNull()->comment('รหัสใบสั่งซื้อ'),
            'journal_trans_id' => $this->integer()->comment('รหัส Journal Transaction (การรับสินค้า)'),
            'check_date' => $this->date()->notNull()->comment('วันที่ตรวจสอบ'),
            'checker_name' => $this->string(100)->comment('ชื่อผู้ตรวจสอบ'),

            // 1. สภาพทั่วไปของสินค้า (15 ข้อ)
            'general_condition' => $this->text()->comment('สภาพทั่วไปของสินค้า (JSON array 15 ข้อ)'),

            // 2. สิ่งที่ถูกต้องตามใบสั่งซื้อ
            'correct_items' => $this->smallInteger()->defaultValue(0)->comment('2.1 สินค้าตรงตาม'),
            'correct_quantity' => $this->smallInteger()->defaultValue(0)->comment('2.2 จำนวนและขนาด'),
            'correct_spec' => $this->smallInteger()->defaultValue(0)->comment('2.3 จำนวนที่สั่ง'),

            // 3. เอกสารที่จัดส่งมาพร้อม
            'has_certificate' => $this->smallInteger()->defaultValue(0)->comment('3.1 ใบ certificate'),
            'has_manual' => $this->smallInteger()->defaultValue(0)->comment('3.2 คู่มือการใช้งาน'),

            'notes' => $this->text()->comment('หมายเหตุ'),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Add foreign key for purch_id (สมมติว่าตารางชื่อ purch)
        $this->addForeignKey(
            'fk-receiving_checklist-purch_id',
            '{{%receiving_checklist}}',
            'purch_id',
            '{{%purch}}', // เปลี่ยนตามชื่อตารางจริง
            'id',
            'CASCADE'
        );

        // Add foreign key for journal_trans_id
        $this->addForeignKey(
            'fk-receiving_checklist-journal_trans_id',
            '{{%receiving_checklist}}',
            'journal_trans_id',
            '{{%journal_trans}}', // เปลี่ยนตามชื่อตารางจริง
            'id',
            'SET NULL'
        );

        // Add indexes
        $this->createIndex(
            'idx-receiving_checklist-purch_id',
            '{{%receiving_checklist}}',
            'purch_id'
        );

        $this->createIndex(
            'idx-receiving_checklist-journal_trans_id',
            '{{%receiving_checklist}}',
            'journal_trans_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-receiving_checklist-journal_trans_id', '{{%receiving_checklist}}');
        $this->dropForeignKey('fk-receiving_checklist-purch_id', '{{%receiving_checklist}}');
        $this->dropIndex('idx-receiving_checklist-journal_trans_id', '{{%receiving_checklist}}');
        $this->dropIndex('idx-receiving_checklist-purch_id', '{{%receiving_checklist}}');
        $this->dropTable('{{%receiving_checklist}}');
    }
}
