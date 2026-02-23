<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%unique_index_from_invoices}}`.
 */
class m260223_025435_drop_unique_index_from_invoices_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('invoice_number_type', 'invoices');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createIndex('invoice_number_type', 'invoices', ['invoice_number', 'invoice_type'], true);
    }
}
