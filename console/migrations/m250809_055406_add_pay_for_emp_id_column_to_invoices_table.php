<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%invoices}}`.
 */
class m250809_055406_add_pay_for_emp_id_column_to_invoices_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoices}}', 'pay_for_emp_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoices}}', 'pay_for_emp_id');
    }
}
