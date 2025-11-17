<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_deposit}}`.
 */
class m251109_134637_create_purch_none_pr_deposit_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_none_pr_deposit}}', [
            'id' => $this->primaryKey(),
            'purchase_master_id' => $this->integer(),
            'trans_date' => $this->datetime(),
            'status' => $this->integer(),
            'amount' => $this->double(),
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
        $this->dropTable('{{%purch_none_pr_deposit}}');
    }
}
