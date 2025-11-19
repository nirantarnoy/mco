<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m251119_093732_add_exchange_rate_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'exchange_rate', $this->double());
        $this->addColumn('{{%purch}}', 'fee_amount', $this->double());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'exchange_rate');
        $this->dropColumn('{{%purch}}', 'fee_amount');
    }
}
