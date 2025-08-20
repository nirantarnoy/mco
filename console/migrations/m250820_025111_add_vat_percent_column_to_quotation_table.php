<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%quotation}}`.
 */
class m250820_025111_add_vat_percent_column_to_quotation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%quotation}}', 'vat_percent', $this->float());
        $this->addColumn('{{%quotation}}', 'vat_total_amount', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%quotation}}', 'vat_percent');
        $this->dropColumn('{{%quotation}}', 'vat_total_amount');
    }
}
