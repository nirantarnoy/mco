<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_activity_status}}`.
 */
class m260818_120000_create_job_activity_status_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->db->getTableSchema('{{%job_activity_status}}');
        if (!$tableSchema) {
            $this->createTable('{{%job_activity_status}}', [
                'id' => $this->primaryKey(),
                'job_id' => $this->integer()->notNull(),
                'step_no' => $this->integer()->notNull(),
                'status' => $this->integer()->defaultValue(0), // 0: Red, 1: Orange, 2: Green, 3: Cancelled
                'remarks' => $this->text()->null(),
                'cancelled_by' => $this->integer()->null(),
                'cancelled_at' => $this->integer()->null(),
                'created_at' => $this->integer()->null(),
                'updated_at' => $this->integer()->null(),
            ]);

            $this->createIndex(
                'idx-job_activity_status-job_id-step_no',
                '{{%job_activity_status}}',
                ['job_id', 'step_no'],
                true
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableSchema = $this->db->getTableSchema('{{%job_activity_status}}');
        if ($tableSchema) {
            $this->dropTable('{{%job_activity_status}}');
        }
    }
}
