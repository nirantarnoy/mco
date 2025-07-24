<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%agency_doc}}`.
 */
class m250724_014516_create_agency_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%agency_doc}}', [
            'id' => $this->primaryKey(),
            'agency_id' => $this->integer(),
            'doc' => $this->string(),
            'type' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%agency_doc}}');
    }
}
