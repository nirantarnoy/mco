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

            // Backfill approve_by and approve_date for existing approved records if null
            $this->execute("UPDATE purchase_master SET approve_by = updated_by, approve_date = FROM_UNIXTIME(updated_at) WHERE approve_status = 1 AND approve_by IS NULL");
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
