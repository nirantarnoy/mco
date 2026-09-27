<?php

use yii\db\Migration;

class m260927_054117_add_rev_to_purchase_master_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purchase_master}}', 'rev', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purchase_master}}', 'rev');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260927_054117_add_rev_to_purchase_master_table cannot be reverted.\n";

        return false;
    }
    */
}
