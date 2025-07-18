<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_req}}`.
 */
class m250718_021927_add_discount_percent_column_to_purch_req_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_req}}', 'discount_percent', $this->float());
        $this->addColumn('{{%purch_req}}', 'vat_percent', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_req}}', 'discount_percent');
        $this->dropColumn('{{%purch_req}}', 'vat_percent');
    }
}
