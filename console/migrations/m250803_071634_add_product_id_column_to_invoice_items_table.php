<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%invoice_items}}`.
 */
class m250803_071634_add_product_id_column_to_invoice_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoice_items}}', 'product_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoice_items}}', 'product_id');
    }
}
