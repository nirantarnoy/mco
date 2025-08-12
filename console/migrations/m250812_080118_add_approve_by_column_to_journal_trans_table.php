<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans}}`.
 */
class m250812_080118_add_approve_by_column_to_journal_trans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans}}', 'approve_by', $this->integer());
        $this->addColumn('{{%journal_trans}}', 'approve_date', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans}}', 'approve_by');
        $this->dropColumn('{{%journal_trans}}', 'approve_date');
    }
}
