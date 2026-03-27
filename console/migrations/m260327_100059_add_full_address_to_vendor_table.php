<?php

use yii\db\Migration;

class m260327_100059_add_full_address_to_vendor_table extends Migration
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
        echo "m260327_100059_add_full_address_to_vendor_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260327_100059_add_full_address_to_vendor_table cannot be reverted.\n";

        return false;
    }
    */
}
