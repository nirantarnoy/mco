<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_salary}}`.
 */
class m260817_170000_create_company_salary_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableNames = $this->db->getSchema()->getTableNames();
        if (!in_array('company_salary', $tableNames)) {
            $this->createTable('{{%company_salary}}', [
                'id' => $this->primaryKey(),
                'company_id' => $this->integer()->notNull(),
                'salary_month' => $this->integer()->notNull(),
                'salary_year' => $this->integer()->notNull(),
                'amount' => $this->decimal(12, 2)->defaultValue(0.00),
                'note' => $this->string(255)->null(),
                'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ]);

            $this->createIndex(
                'idx-company_salary-company_month_year',
                '{{%company_salary}}',
                ['company_id', 'salary_year', 'salary_month'],
                true
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%company_salary}}');
    }
}
