<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%petty_cash_voucher}}`.
 */
class m250826_064527_add_approve_status_column_to_petty_cash_voucher_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%petty_cash_voucher}}', 'approve_status', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%petty_cash_voucher}}', 'approve_status');
    }
}
