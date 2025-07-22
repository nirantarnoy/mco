<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans}}`.
 */
class m250722_121600_add_job_id_column_to_journal_trans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans}}', 'job_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans}}', 'job_id');
    }
}
