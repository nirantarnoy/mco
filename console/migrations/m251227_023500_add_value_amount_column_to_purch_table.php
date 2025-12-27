<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m251227_023500_add_value_amount_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'value_amount', $this->decimal(10, 2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'value_amount');
    }
}
