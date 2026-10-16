<?php

use yii\db\Migration;

/**
 * Class m261016_090000_add_recall_columns_to_all_approval_models
 */
class m261016_090000_add_recall_columns_to_all_approval_models extends Migration
{
    private $tables = [
        'purch_req',
        'purchase_master',
        'quotation',
        'petty_cash_voucher',
        'petty_cash_advance',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->tables as $tableName) {
            $table = $this->db->getTableSchema($tableName);
            if ($table) {
                if (!isset($table->columns['is_recall'])) {
                    $this->addColumn($tableName, 'is_recall', $this->integer()->defaultValue(0));
                }
                if (!isset($table->columns['recall_reason'])) {
                    $this->execute("ALTER TABLE `{$tableName}` ADD `recall_reason` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
                } else {
                    $this->execute("ALTER TABLE `{$tableName}` MODIFY `recall_reason` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
                }
                if (!isset($table->columns['recall_by'])) {
                    $this->addColumn($tableName, 'recall_by', $this->integer()->null());
                }
                if (!isset($table->columns['recall_at'])) {
                    $this->addColumn($tableName, 'recall_at', $this->dateTime()->null());
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
            $table = $this->db->getTableSchema($tableName);
            if ($table) {
                if (isset($table->columns['is_recall'])) {
                    $this->dropColumn($tableName, 'is_recall');
                }
                if (isset($table->columns['recall_reason'])) {
                    $this->dropColumn($tableName, 'recall_reason');
                }
                if (isset($table->columns['recall_by'])) {
                    $this->dropColumn($tableName, 'recall_by');
                }
                if (isset($table->columns['recall_at'])) {
                    $this->dropColumn($tableName, 'recall_at');
                }
            }
        }
    }
}
