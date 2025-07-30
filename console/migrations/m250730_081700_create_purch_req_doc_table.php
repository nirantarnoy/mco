<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_req_doc}}`.
 */
class m250730_081700_create_purch_req_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_req_doc}}', [
            'id' => $this->primaryKey(),
            'purch_req_id' => $this->integer(),
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
        $this->dropTable('{{%purch_req_doc}}');
    }
}
