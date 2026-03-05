<?php

use yii\db\Migration;

class m260305_064312_add_product_name_to_job_line extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%job_line}}', 'product_name', $this->string()->after('product_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%job_line}}', 'product_name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260305_064312_add_product_name_to_job_line cannot be reverted.\n";

        return false;
    }
    */
}
