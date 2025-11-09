<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_deposit_line}}`.
 */
class m251109_135253_create_purch_deposit_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_deposit_line}}', [
            'id' => $this->primaryKey(),
            'purch_deposit_id' => $this->integer(),
            'deposit_amount' => $this->double(),
            'deposit_date' => $this->datetime(),
            'deposit_doc' => $this->string(),
            'receive_date' => $this->datetime(),
            'receive_doc' => $this->string(),
            'note' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch_deposit_line}}');
    }
}
