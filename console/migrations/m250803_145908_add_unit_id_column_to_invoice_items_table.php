<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%invoice_items}}`.
 */
class m250803_145908_add_unit_id_column_to_invoice_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoice_items}}', 'unit_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoice_items}}', 'unit_id');
    }
}
