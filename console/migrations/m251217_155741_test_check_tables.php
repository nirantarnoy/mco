<?php

use yii\db\Migration;

class m251217_155741_test_check_tables extends Migration
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
        echo "m251217_155741_test_check_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251217_155741_test_check_tables cannot be reverted.\n";

        return false;
    }
    */
}
