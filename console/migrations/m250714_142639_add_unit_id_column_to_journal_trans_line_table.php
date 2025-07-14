<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans_line}}`.
 */
class m250714_142639_add_unit_id_column_to_journal_trans_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans_line}}', 'unit_id', $this->integer());
        $this->addColumn('{{%journal_trans_line}}', 'line_total', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans_line}}', 'unit_id');
        $this->dropColumn('{{%journal_trans_line}}', 'line_total');
    }
}
