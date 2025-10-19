<?php

use yii\db\Migration;

class m251018_151147_add_company_id_column_to_all_tables extends Migration
{
    // ใช้ชื่อตารางแบบไม่มี {{%}} เลย
    private $tables = [
        'customer', 'product', 'purch', 'purch_req', 'job', 'quotation',
        'journal_trans', 'employee', 'invoices', 'stock_sum', 'stock_trans', 'vendor',
        'warehouse', 'employer', 'job_expense', 'payment_receipts', 'debit_note', 'credit_note',
        'billing_invoices', 'petty_cash_advance', 'petty_cash_voucher'
    ];
//    private $tables = [
//        'purch', 'purch_req', 'job', 'quotation',
//        'journal_trans', 'employee', 'invoices', 'stock_sum', 'stock_trans', 'vendor',
//        'warehouse', 'employer', 'job_expense', 'payment_receipts', 'debit_note', 'credit_note',
//        'billing_invoices', 'petty_cash_advance', 'petty_cash_voucher'
//    ];

    public function safeUp()
    {
        $columnType = $this->integer()->notNull()->defaultValue(0)->comment('Company ID');

        foreach ($this->tables as $tableName) {
            // ใช้ {{%...}} เฉพาะตอนเรียก method
            $table = "{{%{$tableName}}}";

            $this->addColumn($table, 'company_id', $columnType);

            // ใช้ underscore แทน dash ในชื่อ index
            $indexName = "idx_{$tableName}_company_id";
            $this->createIndex($indexName, $table, 'company_id');
        }
    }

    public function safeDown()
    {
        foreach ($this->tables as $tableName) {
            $table = "{{%{$tableName}}}";
            $indexName = "idx_{$tableName}_company_id";

            $this->dropIndex($indexName, $table);
            $this->dropColumn($table, 'company_id');
        }
    }
}
