<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch-payment}}`.
 */
class m251104_154104_create_purch_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch-payment}}', [
            'id' => $this->primaryKey(),
            'purch_id' => $this->integer(),
            'trans_date' => $this->datetime(),
            'status' => $this->integer(),
            'created_by' => $this->integer(),
            'created_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch-payment}}');
    }
}
