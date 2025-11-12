<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%general_payment}}`.
 */
class m251111_094847_create_general_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%general_payment}}', [
            'id' => $this->primaryKey(),
            'journal_no' => $this->string(),
            'company_id' => $this->integer(),
            'trans_date' => $this->datetime(),
            'description' => $this->string(),
            'status' => $this->integer(),
            'total_amount' => $this->double(),
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
        $this->dropTable('{{%general_payment}}');
    }
}
