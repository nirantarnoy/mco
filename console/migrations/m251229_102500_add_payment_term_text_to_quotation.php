<?php

use yii\db\Migration;

/**
 * Class m251229_102500_add_payment_term_text_to_quotation
 */
class m251229_102500_add_payment_term_text_to_quotation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%quotation}}', 'payment_term_text', $this->string()->comment('เงื่อนไขการชำระเงิน (ข้อความ)'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%quotation}}', 'payment_term_text');
    }
}
