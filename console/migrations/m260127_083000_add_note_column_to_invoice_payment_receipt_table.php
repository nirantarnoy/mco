<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%invoice_payment_receipt}}`.
 */
class m260127_083000_add_note_column_to_invoice_payment_receipt_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoice_payment_receipt}}', 'note', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoice_payment_receipt}}', 'note');
    }
}
