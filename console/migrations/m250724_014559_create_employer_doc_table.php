<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employer_doc}}`.
 */
class m250724_014559_create_employer_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employer_doc}}', [
            'id' => $this->primaryKey(),
            'employer_id' => $this->integer(),
            'doc' => $this->string(),
            'type' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%employer_doc}}');
    }
}
