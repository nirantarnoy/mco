<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%monthly_account_closing}}`.
 */
class m260818_120100_create_monthly_account_closing_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->db->getTableSchema('{{%monthly_account_closing}}');
        if (!$tableSchema) {
            $this->createTable('{{%monthly_account_closing}}', [
                'id' => $this->primaryKey(),
                'company_id' => $this->integer()->null(),
                'year_month' => $this->string(10)->notNull(), // e.g. '2026-08'
                'petty_cash_balance' => $this->decimal(12, 2)->defaultValue(0),
                'main_account_balance' => $this->decimal(12, 2)->defaultValue(0),
                'statement_file' => $this->string(255)->null(),
                'closed_by' => $this->integer()->null(),
                'closed_at' => $this->integer()->null(),
                'remarks' => $this->text()->null(),
            ]);

            $this->createIndex(
                'idx-monthly_account_closing-comp-ym',
                '{{%monthly_account_closing}}',
                ['company_id', 'year_month']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableSchema = $this->db->getTableSchema('{{%monthly_account_closing}}');
        if ($tableSchema) {
            $this->dropTable('{{%monthly_account_closing}}');
        }
    }
}
