<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%worker_doc}}`.
 */
class m250724_014456_create_worker_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%worker_doc}}', [
            'id' => $this->primaryKey(),
            'worker_id' => $this->integer(),
            'doc' => $this->string(),
            'type' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%worker_doc}}');
    }
}
