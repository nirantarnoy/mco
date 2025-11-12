<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%general_payment_line}}`.
 */
class m251111_095103_create_general_payment_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%general_payment_line}}', [
            'id' => $this->primaryKey(),
            'general_payment_id' => $this->integer(),
            'description' => $this->string(),
            'bank_id' => $this->integer(),
            'bank_name' => $this->string(),
            'payment_method_id' => $this->integer(),
            'pay_amount' => $this->double(),
            'doc' => $this->string(),
            'note' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%general_payment_line}}');
    }
}
