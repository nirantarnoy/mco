<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250704_092551_add_discount_amount_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'discount_amount', $this->float());
        $this->addColumn('{{%purch}}', 'vat_amount', $this->float());
        $this->addColumn('{{%purch}}', 'net_amount', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'discount_amount');
        $this->dropColumn('{{%purch}}', 'vat_amount');
        $this->dropColumn('{{%purch}}', 'net_amount');
    }
}
