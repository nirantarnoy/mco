<?php

use yii\db\Migration;

class m260807_123155_add_lot_no_to_inventory_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('journal_trans_line', 'lot_no', $this->string(50)->comment('Lot No.'));
        $this->addColumn('stock_trans', 'lot_no', $this->string(50)->comment('Lot No.'));
        $this->addColumn('stock_sum', 'lot_no', $this->string(50)->comment('Lot No.'));

        // Try to drop existing index
        try {
            $this->dropIndex('product_id', 'stock_sum');
        } catch (\Exception $e) {}
        try {
            $this->dropIndex('product_id_2', 'stock_sum');
        } catch (\Exception $e) {}

        // Add new unique index
        $this->createIndex(
            'idx_stock_sum_unique',
            'stock_sum',
            ['product_id', 'warehouse_id', 'lot_no'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_stock_sum_unique', 'stock_sum');
        $this->dropColumn('stock_sum', 'lot_no');
        $this->dropColumn('stock_trans', 'lot_no');
        $this->dropColumn('journal_trans_line', 'lot_no');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260807_123155_add_lot_no_to_inventory_tables cannot be reverted.\n";

        return false;
    }
    */
}
