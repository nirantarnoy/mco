<?php

use yii\db\Migration;

class m260406_063418_add_approve_status_to_purchase_master_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purchase_master}}', 'approve_status', $this->integer()->defaultValue(0)->after('status')->comment('สถานะอนุมัติ 0=รอ, 1=อนุมัติ, 2=ไม่อนุมัติ'));
        
        // Migrate data
        // status 2 (Approved in old system) -> active status (1) and approve_status 1 (Approved)
        $this->update('{{%purchase_master}}', ['status' => 1, 'approve_status' => 1], ['status' => 2]);
        
        // status 0 (Cancelled in old system) -> status 3 (new cancelled for PR/PO alignment)
        $this->update('{{%purchase_master}}', ['status' => 3], ['status' => 0]);
        
        // status 1 remains 1 (Active/Open)
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revert status values
        $this->update('{{%purchase_master}}', ['status' => 0], ['status' => 3]);
        $this->update('{{%purchase_master}}', ['status' => 2], ['status' => 1, 'approve_status' => 1]);
        
        $this->dropColumn('{{%purchase_master}}', 'approve_status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260406_063418_add_approve_status_to_purchase_master_table cannot be reverted.\n";

        return false;
    }
    */
}
