<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans}}`.
 */
class m250715_025448_add_return_for_trans_id_column_to_journal_trans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans}}', 'return_for_trans_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans}}', 'return_for_trans_id');
    }
}
