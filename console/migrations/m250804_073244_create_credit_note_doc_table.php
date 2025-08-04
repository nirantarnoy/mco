<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%credit_note_doc}}`.
 */
class m250804_073244_create_credit_note_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%credit_note_doc}}', [
            'id' => $this->primaryKey(),
            'credit_note_id' => $this->integer(),
            'doc' => $this->string(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%credit_note_doc}}');
    }
}
