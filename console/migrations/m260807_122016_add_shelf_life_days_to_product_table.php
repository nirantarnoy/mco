<?php

use yii\db\Migration;

class m260807_122016_add_shelf_life_days_to_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product}}', 'shelf_life_days', $this->integer()->null()->comment('อายุการเก็บรักษา (วัน)'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%product}}', 'shelf_life_days');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260807_122016_add_shelf_life_days_to_product_table cannot be reverted.\n";

        return false;
    }
    */
}
