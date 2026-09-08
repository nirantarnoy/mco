<?php

use yii\db\Migration;

class m260908_014220_add_checker_and_approver_to_pre_advance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pre_advance', 'checked_by', $this->integer()->null());
        $this->addColumn('pre_advance', 'checked_at', $this->dateTime()->null());
        $this->addColumn('pre_advance', 'approved_by', $this->integer()->null());
        $this->addColumn('pre_advance', 'approved_at', $this->dateTime()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pre_advance', 'checked_by');
        $this->dropColumn('pre_advance', 'checked_at');
        $this->dropColumn('pre_advance', 'approved_by');
        $this->dropColumn('pre_advance', 'approved_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260908_014220_add_checker_and_approver_to_pre_advance_table cannot be reverted.\n";

        return false;
    }
    */
}
