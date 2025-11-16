<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%quotation}}`.
 */
class m251116_151334_add_currency_id_column_to_quotation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%quotation}}', 'currency_id', $this->integer());
        $this->addColumn('{{%quotation}}', 'customer_tax_id', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%quotation}}', 'currency_id');
        $this->dropColumn('{{%quotation}}', 'customer_tax_id');
    }
}
