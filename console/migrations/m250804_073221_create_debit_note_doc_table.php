<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%debit_note_doc}}`.
 */
class m250804_073221_create_debit_note_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%debit_note_doc}}', [
            'id' => $this->primaryKey(),
            'debit_note_id' => $this->integer(),
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
        $this->dropTable('{{%debit_note_doc}}');
    }
}
