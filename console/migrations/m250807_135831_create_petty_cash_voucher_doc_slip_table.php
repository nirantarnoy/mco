<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%petty_cash_voucher_doc_slip}}`.
 */
class m250807_135831_create_petty_cash_voucher_doc_slip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%petty_cash_voucher_doc_slip}}', [
            'id' => $this->primaryKey(),
            'petty_cash_voucher_id' => $this->integer(),
            'doc' => $this->string(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%petty_cash_voucher_doc_slip}}');
    }
}
