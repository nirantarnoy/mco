<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%petty_cash_voucher}}`.
 */
class m251025_120123_add_updated_by_column_to_petty_cash_voucher_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%petty_cash_voucher}}', 'updated_by', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%petty_cash_voucher}}', 'updated_by');
    }
}
