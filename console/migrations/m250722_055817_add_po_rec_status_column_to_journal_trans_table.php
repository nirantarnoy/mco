<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans}}`.
 */
class m250722_055817_add_po_rec_status_column_to_journal_trans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans}}', 'po_rec_status', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans}}', 'po_rec_status');
    }
}
