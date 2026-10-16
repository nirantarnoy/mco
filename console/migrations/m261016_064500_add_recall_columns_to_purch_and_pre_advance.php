<?php

use yii\db\Migration;

/**
 * Class m261016_064500_add_recall_columns_to_purch_and_pre_advance
 */
class m261016_064500_add_recall_columns_to_purch_and_pre_advance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablePurch = $this->db->getTableSchema('purch');
        if ($tablePurch) {
            if (!isset($tablePurch->columns['is_recall'])) {
                $this->addColumn('purch', 'is_recall', $this->integer()->defaultValue(0));
            }
            if (!isset($tablePurch->columns['recall_reason'])) {
                $this->addColumn('purch', 'recall_reason', $this->text()->null());
            }
            if (!isset($tablePurch->columns['recall_by'])) {
                $this->addColumn('purch', 'recall_by', $this->integer()->null());
            }
            if (!isset($tablePurch->columns['recall_at'])) {
                $this->addColumn('purch', 'recall_at', $this->dateTime()->null());
            }
        }

        $tablePreAdvance = $this->db->getTableSchema('pre_advance');
        if ($tablePreAdvance) {
            if (!isset($tablePreAdvance->columns['is_recall'])) {
                $this->addColumn('pre_advance', 'is_recall', $this->integer()->defaultValue(0));
            }
            if (!isset($tablePreAdvance->columns['recall_reason'])) {
                $this->addColumn('pre_advance', 'recall_reason', $this->text()->null());
            }
            if (!isset($tablePreAdvance->columns['recall_by'])) {
                $this->addColumn('pre_advance', 'recall_by', $this->integer()->null());
            }
            if (!isset($tablePreAdvance->columns['recall_at'])) {
                $this->addColumn('pre_advance', 'recall_at', $this->dateTime()->null());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tablePurch = $this->db->getTableSchema('purch');
        if ($tablePurch) {
            if (isset($tablePurch->columns['is_recall'])) {
                $this->dropColumn('purch', 'is_recall');
            }
            if (isset($tablePurch->columns['recall_reason'])) {
                $this->dropColumn('purch', 'recall_reason');
            }
            if (isset($tablePurch->columns['recall_by'])) {
                $this->dropColumn('purch', 'recall_by');
            }
            if (isset($tablePurch->columns['recall_at'])) {
                $this->dropColumn('purch', 'recall_at');
            }
        }

        $tablePreAdvance = $this->db->getTableSchema('pre_advance');
        if ($tablePreAdvance) {
            if (isset($tablePreAdvance->columns['is_recall'])) {
                $this->dropColumn('pre_advance', 'is_recall');
            }
            if (isset($tablePreAdvance->columns['recall_reason'])) {
                $this->dropColumn('pre_advance', 'recall_reason');
            }
            if (isset($tablePreAdvance->columns['recall_by'])) {
                $this->dropColumn('pre_advance', 'recall_by');
            }
            if (isset($tablePreAdvance->columns['recall_at'])) {
                $this->dropColumn('pre_advance', 'recall_at');
            }
        }
    }
}
