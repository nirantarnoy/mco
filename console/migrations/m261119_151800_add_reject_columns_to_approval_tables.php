<?php

use yii\db\Migration;

/**
 * Class m261119_151800_add_reject_columns_to_approval_tables
 */
class m261119_151800_add_reject_columns_to_approval_tables extends Migration
{
    private $tables = [
        'purch',
        'purchase_master',
        'pre_advance',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->tables as $tableName) {
            $table = $this->db->getTableSchema($tableName, true);
            if ($table) {
                if (!isset($table->columns['reject_reason'])) {
                    $this->execute("ALTER TABLE `{$tableName}` ADD `reject_reason` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
                } else {
                    $this->execute("ALTER TABLE `{$tableName}` MODIFY `reject_reason` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
                }
                if (!isset($table->columns['rejected_by'])) {
                    $this->addColumn($tableName, 'rejected_by', $this->integer()->null());
                }
                if (!isset($table->columns['rejected_at'])) {
                    $this->addColumn($tableName, 'rejected_at', $this->dateTime()->null());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->tables as $tableName) {
            $table = $this->db->getTableSchema($tableName, true);
            if ($table) {
                if (isset($table->columns['reject_reason'])) {
                    $this->dropColumn($tableName, 'reject_reason');
                }
                if (isset($table->columns['rejected_by'])) {
                    $this->dropColumn($tableName, 'rejected_by');
                }
                if (isset($table->columns['rejected_at'])) {
                    $this->dropColumn($tableName, 'rejected_at');
                }
            }
        }
    }
}
