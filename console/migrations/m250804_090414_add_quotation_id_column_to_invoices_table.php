<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%invoices}}`.
 */
class m250804_090414_add_quotation_id_column_to_invoices_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoices}}', 'quotation_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoices}}', 'quotation_id');
    }
}
