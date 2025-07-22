<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%worker}}`.
 */
class m250721_083606_create_worker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%worker}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'fnam' => $this->string(),
            'lname' => $this->string(),
            'description' => $this->string(),
            'phone' => $this->string(),
            'idcard_no' => $this->string(),
            'doc' => $this->string(),
            'status' => $this->integer(),
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
        $this->dropTable('{{%worker}}');
    }
}
