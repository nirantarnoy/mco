<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_line}}`.
 */
class m251227_024000_add_value_amount_column_to_purch_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_line}}', 'value_amount', $this->decimal(10, 2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_line}}', 'value_amount');
    }
}
