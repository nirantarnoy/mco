<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%stock_sum}}`.
 */
class m250710_164144_add_created_at_column_to_stock_sum_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%stock_sum}}', 'created_at', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%stock_sum}}', 'created_at');
    }
}
