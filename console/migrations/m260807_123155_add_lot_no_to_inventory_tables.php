<?php

use yii\db\Migration;

class m260807_123155_add_lot_no_to_inventory_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260807_123155_add_lot_no_to_inventory_tables cannot be reverted.\n";

        return false;
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
