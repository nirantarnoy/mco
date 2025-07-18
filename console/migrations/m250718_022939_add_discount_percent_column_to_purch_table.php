<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250718_022939_add_discount_percent_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'discount_percent', $this->float());
        $this->addColumn('{{%purch}}', 'vat_percent', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'discount_percent');
        $this->dropColumn('{{%purch}}', 'vat_percent');
    }
}
