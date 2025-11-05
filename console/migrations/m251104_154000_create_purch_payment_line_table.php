<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_payment_line}}`.
 */
class m251104_154000_create_purch_payment_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_payment_line}}', [
            'id' => $this->primaryKey(),
            'purch_payment_id' => $this->integer(),
            'bank_id' => $this->integer(),
            'bank_name' => $this->string(),
            'payment_method_id' => $this->integer(),
            'pay_amount' => $this->double(),
            'doc' => $this->string(),
            'nodet' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch_payment_line}}');
    }
}
