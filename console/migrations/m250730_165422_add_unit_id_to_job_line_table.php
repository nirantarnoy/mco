<?php

use yii\db\Migration;

class m250730_165422_add_unit_id_to_job_line_table extends Migration
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
        echo "m250730_165422_add_unit_id_to_job_line_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250730_165422_add_unit_id_to_job_line_table cannot be reverted.\n";

        return false;
    }
    */
}
