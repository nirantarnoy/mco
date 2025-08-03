<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250803_030713_add_ref_no_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'ref_no', $this->string());
        $this->addColumn('{{%purch}}', 'footer_delivery', $this->string());
        $this->addColumn('{{%purch}}', 'footer_payment', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'ref_no');
        $this->dropColumn('{{%purch}}', 'footer_delivery');
        $this->dropColumn('{{%purch}}', 'footer_payment');
    }
}
