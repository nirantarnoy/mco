<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bank_account}}`.
 */
class m260217_134152_create_bank_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bank_account}}', [
            'id' => $this->primaryKey(),
            'bank_name' => $this->string(),
            'account_no' => $this->string(),
            'account_name' => $this->string(),
            'branch' => $this->string(),
            'status' => $this->integer()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bank_account}}');
    }
}
