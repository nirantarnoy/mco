<?php

use yii\db\Migration;

class m261105_150305_add_has_vat_to_purchase_detail_and_invoice_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table1 = $this->db->getTableSchema('purchase_detail');
        if ($table1 && !isset($table1->columns['has_vat'])) {
            $this->addColumn('purchase_detail', 'has_vat', $this->smallInteger()->defaultValue(1)->comment('1=Calculate VAT, 0=No VAT'));
        }

        $table2 = $this->db->getTableSchema('invoice_items');
        if ($table2 && !isset($table2->columns['has_vat'])) {
            $this->addColumn('invoice_items', 'has_vat', $this->smallInteger()->defaultValue(1)->comment('1=Calculate VAT, 0=No VAT'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table1 = $this->db->getTableSchema('purchase_detail');
        if ($table1 && isset($table1->columns['has_vat'])) {
            $this->dropColumn('purchase_detail', 'has_vat');
        }

        $table2 = $this->db->getTableSchema('invoice_items');
        if ($table2 && isset($table2->columns['has_vat'])) {
            $this->dropColumn('invoice_items', 'has_vat');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261105_150305_add_has_vat_to_purchase_detail_and_invoice_items cannot be reverted.\n";

        return false;
    }
    */
}
