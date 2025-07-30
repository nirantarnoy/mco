<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans_aricat}}`.
 */
class m250730_062300_add_note_column_to_journal_trans_aricat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans_aricat}}', 'note', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans_aricat}}', 'note');
    }
}
