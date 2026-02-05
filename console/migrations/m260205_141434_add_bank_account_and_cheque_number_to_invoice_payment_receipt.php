<?php

use yii\db\Migration;

class m260205_141434_add_bank_account_and_cheque_number_to_invoice_payment_receipt extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('invoice_payment_receipt', 'bank_account', $this->string()->null());
        $this->addColumn('invoice_payment_receipt', 'cheque_number', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('invoice_payment_receipt', 'bank_account');
        $this->dropColumn('invoice_payment_receipt', 'cheque_number');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260205_141434_add_bank_account_and_cheque_number_to_invoice_payment_receipt cannot be reverted.\n";

        return false;
    }
    */
}
