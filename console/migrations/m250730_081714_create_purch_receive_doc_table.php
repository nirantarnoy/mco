<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_doc}}`.
 */
class m250730_081714_create_purch_receive_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_receive_doc}}', [
            'id' => $this->primaryKey(),
            'purch_id' => $this->integer(),
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
        $this->dropTable('{{%purch_receive_doc}}');
    }
}
