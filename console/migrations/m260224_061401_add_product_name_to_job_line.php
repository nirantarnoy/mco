<?php

use yii\db\Migration;

class m260224_061401_add_product_name_to_job_line extends Migration
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
        echo "m260224_061401_add_product_name_to_job_line cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260224_061401_add_product_name_to_job_line cannot be reverted.\n";

        return false;
    }
    */
}
