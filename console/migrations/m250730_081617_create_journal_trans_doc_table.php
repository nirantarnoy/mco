<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%journal_trans_doc}}`.
 */
class m250730_081617_create_journal_trans_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%journal_trans_doc}}', [
            'id' => $this->primaryKey(),
            'journal_trans_id' => $this->integer(),
            'doc_name' => $this->string(),
            'note' => $this->string(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%journal_trans_doc}}');
    }
}
