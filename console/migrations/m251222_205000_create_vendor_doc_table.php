<?php

use yii\db\Migration;

/**
 * Class m251222_205000_create_vendor_doc_table
 */
class m251222_205000_create_vendor_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%vendor_doc}}', [
            'id' => $this->primaryKey(),
            'vendor_id' => $this->integer(),
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
        $this->dropTable('{{%vendor_doc}}');
    }
}
