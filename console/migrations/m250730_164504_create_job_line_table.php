<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_line}}`.
 */
class m250730_164504_create_job_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_line}}', [
            'id' => $this->primaryKey(),
            'job_id' => $this->integer(),
            'product_id' => $this->integer(),
            'qty' => $this->float(),
            'line_price' => $this->float(),
            'line_total' => $this->float(),
            'status' => $this->integer(),
            'note' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%job_line}}');
    }
}
