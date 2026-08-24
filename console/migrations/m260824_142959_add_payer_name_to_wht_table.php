<?php

use yii\db\Migration;

class m260824_142959_add_payer_name_to_wht_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('wht', 'payer_name', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('wht', 'payer_name');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260824_142959_add_payer_name_to_wht_table cannot be reverted.\n";

        return false;
    }
    */
}
