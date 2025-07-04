<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job}}`.
 */
class m250702_145325_create_job_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job}}', [
            'id' => $this->primaryKey(),
            'job_no' => $this->string(),
            'job_date' => $this->datetime(),
            'customer_id' => $this->integer(),
            'customer_name'=> $this->string(),
            'quote_no' => $this->string(),
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
        $this->dropTable('{{%job}}');
    }
}
