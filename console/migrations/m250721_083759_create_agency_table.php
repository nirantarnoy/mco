<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%agency}}`.
 */
class m250721_083759_create_agency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%agency}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->string(),
            'phone' => $this->string(),
            'idcard_no' => $this->string(),
            'doc' => $this->string(),
            'status' => $this->integer(),
            'emp_id' => $this->integer(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%agency}}');
    }
}
