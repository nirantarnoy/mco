<?php

use yii\db\Migration;

class m260223_025007_check_db_schema extends Migration
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
        echo "m260223_025007_check_db_schema cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260223_025007_check_db_schema cannot be reverted.\n";

        return false;
    }
    */
}
