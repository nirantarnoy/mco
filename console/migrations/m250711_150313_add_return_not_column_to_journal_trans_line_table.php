<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans_line}}`.
 */
class m250711_150313_add_return_not_column_to_journal_trans_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans_line}}', 'return_not', $this->string());
        $this->addColumn('{{%journal_trans_line}}', 'good_qty', $this->float());
        $this->addColumn('{{%journal_trans_line}}', 'damaged_qty', $this->float());
        $this->addColumn('{{%journal_trans_line}}', 'missin_qty', $this->float());
        $this->addColumn('{{%journal_trans_line}}', 'condition_note', $this->string());
        $this->addColumn('{{%journal_trans_line}}', 'item_condition', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans_line}}', 'return_not');
        $this->dropColumn('{{%journal_trans_line}}', 'good_qty');
        $this->dropColumn('{{%journal_trans_line}}', 'damaged_qty');
        $this->dropColumn('{{%journal_trans_line}}', 'missin_qty');
        $this->dropColumn('{{%journal_trans_line}}', 'condition_note');
        $this->dropColumn('{{%journal_trans_line}}', 'item_condition');
    }
}
