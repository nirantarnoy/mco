<?php

use yii\db\Migration;

class m250713_045251_add_vendor_group_table_to_vendor_table extends Migration
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
        echo "m250713_045251_add_vendor_group_table_to_vendor_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250713_045251_add_vendor_group_table_to_vendor_table cannot be reverted.\n";

        return false;
    }
    */
}
