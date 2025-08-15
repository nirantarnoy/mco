<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250815_043942_add_discount_total_amount_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'discount_total_amount', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'discount_total_amount');
    }
}
