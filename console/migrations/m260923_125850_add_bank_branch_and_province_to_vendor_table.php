<?php

use yii\db\Migration;

class m260923_125850_add_bank_branch_and_province_to_vendor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('vendor', 'bank_branch', $this->string(255)->null());
        $this->addColumn('vendor', 'bank_province', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('vendor', 'bank_branch');
        $this->dropColumn('vendor', 'bank_province');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260923_125850_add_bank_branch_and_province_to_vendor_table cannot be reverted.\n";

        return false;
    }
    */
}
