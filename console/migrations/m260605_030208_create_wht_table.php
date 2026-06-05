<?php

use yii\db\Migration;

/**
 * Class m260605_030208_create_wht_table
 */
class m260605_030208_create_wht_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wht}}', [
            'id' => $this->primaryKey(),
            'wht_no' => $this->string(50)->null()->unique(),
            'trans_date' => $this->date()->null(),
            'ref_type' => $this->string(50)->null(),
            'ref_id' => $this->integer()->null(),
            'vendor_id' => $this->integer()->null(),
            'wht_type' => $this->integer()->null(), // 3=ภงด3, 53=ภงด53
            'pay_condition' => $this->integer()->defaultValue(1), // 1=หัก ณ ที่จ่าย
            'base_amount' => $this->decimal(10, 2)->null()->defaultValue(0),
            'wht_percent' => $this->decimal(5, 2)->null()->defaultValue(0),
            'wht_amount' => $this->decimal(10, 2)->null()->defaultValue(0),
            'wht_desc' => $this->string(255)->null(), // ประเภทเงินได้
            'other_desc' => $this->string(255)->null(), // กรณีเลือกอื่นๆ
            'status' => $this->integer()->defaultValue(1),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'company_id' => $this->integer()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wht}}');
    }
}
