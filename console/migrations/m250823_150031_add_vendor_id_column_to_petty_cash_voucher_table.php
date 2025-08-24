<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%petty_cash_voucher}}`.
 */
class m250823_150031_add_vendor_id_column_to_petty_cash_voucher_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%petty_cash_voucher}}', 'vendor_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%petty_cash_voucher}}', 'vendor_id');
    }
}
