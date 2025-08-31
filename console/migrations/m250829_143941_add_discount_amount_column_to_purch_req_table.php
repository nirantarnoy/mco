<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_req}}`.
 */
class m250829_143941_add_discount_amount_column_to_purch_req_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_req}}', 'discount_amount', $this->float());
        $this->addColumn('{{%purch_req}}', 'discount_total_amount', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_req}}', 'discount_amount');
        $this->dropColumn('{{%purch_req}}', 'discount_total_amount');
    }
}
