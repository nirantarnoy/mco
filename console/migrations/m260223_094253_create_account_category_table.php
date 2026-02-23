<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%account_category}}`.
 */
class m260223_094253_create_account_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%account_category}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(50),
            'name' => $this->string(255),
            'description' => $this->text(),
            'status' => $this->integer()->defaultValue(1),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%account_category}}');
    }
}
