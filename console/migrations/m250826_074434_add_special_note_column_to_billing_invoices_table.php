<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%billing_invoices}}`.
 */
class m250826_074434_add_special_note_column_to_billing_invoices_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%billing_invoices}}', 'special_note', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%billing_invoices}}', 'special_note');
    }
}
