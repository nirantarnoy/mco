<?php

use yii\db\Migration;

/**
 * Class m261107_104717_add_approve_columns_to_purchase_master_table
 */
class m261107_104717_add_approve_columns_to_purchase_master_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = $this->db->getTableSchema('purchase_master');
        if ($table) {
            if (!isset($table->columns['approve_by'])) {
                $this->addColumn('purchase_master', 'approve_by', $this->integer()->null()->after('approve_status'));
            }
            if (!isset($table->columns['approve_date'])) {
                $this->addColumn('purchase_master', 'approve_date', $this->dateTime()->null()->after('approve_by'));
            }

            // Set approve_by = 5 and backfill approve_date for all existing approved records
            $this->execute("UPDATE purchase_master SET approve_by = 5, approve_date = COALESCE(approve_date, FROM_UNIXTIME(updated_at), NOW()) WHERE approve_status = 1");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = $this->db->getTableSchema('purchase_master');
        if ($table) {
            if (isset($table->columns['approve_date'])) {
                $this->dropColumn('purchase_master', 'approve_date');
            }
            if (isset($table->columns['approve_by'])) {
                $this->dropColumn('purchase_master', 'approve_by');
            }
        }
    }
}

