<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_expense}}`.
 */
class m251017_062441_create_job_expense_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_expense}}', [
            'id' => $this->primaryKey(),
            'job_id' => $this->integer(),
            'trans_date' => $this->datetime(),
            'description' => $this->string(),
            'line_amount' => $this->float(),
            'line_doc' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%job_expense}}');
    }
}
