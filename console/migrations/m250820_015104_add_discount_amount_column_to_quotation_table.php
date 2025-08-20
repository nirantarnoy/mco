<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%quoation}}`.
 */
class m250820_015104_add_discount_amount_column_to_quotation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%quotation}}', 'discount_amount', $this->float());
        $this->addColumn('{{%quotation}}', 'discount_percent', $this->float());
        $this->addColumn('{{%quotation}}', 'total_discount_amount', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%quotation}}', 'discount_amount');
        $this->dropColumn('{{%quotation}}', 'discount_percent');
        $this->dropColumn('{{%quotation}}', 'total_discount_amount');
    }
}
