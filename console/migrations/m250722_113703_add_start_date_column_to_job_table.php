<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%job}}`.
 */
class m250722_113703_add_start_date_column_to_job_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%job}}', 'start_date', $this->datetime());
        $this->addColumn('{{%job}}', 'end_date', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%job}}', 'start_date');
        $this->dropColumn('{{%job}}', 'end_date');
    }
}
