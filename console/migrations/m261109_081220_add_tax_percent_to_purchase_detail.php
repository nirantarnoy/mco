<?php

use yii\db\Migration;

class m261109_081220_add_tax_percent_to_purchase_detail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = $this->db->getTableSchema('purchase_detail');
        if ($table && !isset($table->columns['tax_percent'])) {
            $this->addColumn('purchase_detail', 'tax_percent', $this->decimal(5, 2)->defaultValue(0)->comment('TAX/WHT % (1, 2, 3, 5, etc.)'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = $this->db->getTableSchema('purchase_detail');
        if ($table && !isset($table->columns['tax_percent'])) {
            $this->dropColumn('purchase_detail', 'tax_percent');
        }
    }
}
