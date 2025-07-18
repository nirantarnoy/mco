<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%journal_trans_line}}`.
 */
class m250619_014552_create_journal_trans_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%journal_trans_line}}', [
            'id' => $this->primaryKey(),
            'journal_trans_id' => $this->integer(),
            'product_id' => $this->integer(),
            'warehouse_id' => $this->integer(),
            'qty' => $this->float(),
            'remark' => $this->string(),
            'status' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%journal_trans_line}}');
    }
}
