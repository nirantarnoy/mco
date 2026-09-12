<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purchase_master}}`.
 */
class m260912_071210_add_currency_columns_to_purchase_master_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purchase_master}}', 'currency_id', $this->integer()->defaultValue(1)->comment('สกุลเงิน'));
        $this->addColumn('{{%purchase_master}}', 'exchange_rate', $this->decimal(10, 4)->defaultValue(1.0000)->comment('อัตราแลกเปลี่ยน'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purchase_master}}', 'exchange_rate');
        $this->dropColumn('{{%purchase_master}}', 'currency_id');
    }
}
