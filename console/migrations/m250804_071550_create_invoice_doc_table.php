<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice_doc}}`.
 */
class m250804_071550_create_invoice_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invoice_doc}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer(),
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
        $this->dropTable('{{%invoice_doc}}');
    }
}
