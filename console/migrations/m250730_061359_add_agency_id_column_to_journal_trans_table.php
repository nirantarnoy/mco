<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%journal_trans}}`.
 */
class m250730_061359_add_agency_id_column_to_journal_trans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal_trans}}', 'agency_id', $this->integer());
        $this->addColumn('{{%journal_trans}}', 'employer_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%journal_trans}}', 'agency_id');
        $this->dropColumn('{{%journal_trans}}', 'employer_id');
    }
}
