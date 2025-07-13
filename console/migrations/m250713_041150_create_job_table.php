<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job}}`.
 */
class m250713_041150_create_job_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job}}', [
            'id' => $this->primaryKey(),
            'job_no' => $this->string(),
            'quotation_id' => $this->integer(),
            'job_date' => $this->datetime(),
            'status' => $this->integer(),
            'job_amount' => $this->float(),
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
