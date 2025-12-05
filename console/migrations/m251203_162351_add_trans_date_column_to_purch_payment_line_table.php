<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_payment_line}}`.
 */
class m251203_162351_add_trans_date_column_to_purch_payment_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_payment_line}}', 'trans_date', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_payment_line}}', 'trans_date');
    }
}
