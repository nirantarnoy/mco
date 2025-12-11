<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%petty_cash_detail}}`.
 */
class m251211_070015_add_vat_prohibit_column_to_petty_cash_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%petty_cash_detail}}', 'vat_prohibit', $this->decimal());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%petty_cash_detail}}', 'vat_prohibit');
    }
}
