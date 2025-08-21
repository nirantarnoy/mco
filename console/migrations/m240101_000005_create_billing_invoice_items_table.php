<?php
use yii\db\Migration;

class m240101_000005_create_billing_invoice_items_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%billing_invoice_items}}', [
            'id' => $this->primaryKey(),
            'billing_invoice_id' => $this->integer()->notNull(),
            'invoice_id' => $this->integer()->notNull(),
            'item_seq' => $this->integer(3)->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'sort_order' => $this->integer(3)->defaultValue(0),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Add indexes
        $this->createIndex('idx-billing_invoice_items-billing_invoice_id', '{{%billing_invoice_items}}', 'billing_invoice_id');
        $this->createIndex('idx-billing_invoice_items-invoice_id', '{{%billing_invoice_items}}', 'invoice_id');

        // Add foreign keys
        $this->addForeignKey(
            'fk-billing_invoice_items-billing_invoice_id',
            '{{%billing_invoice_items}}',
            'billing_invoice_id',
            '{{%billing_invoices}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-billing_invoice_items-invoice_id',
            '{{%billing_invoice_items}}',
            'invoice_id',
            '{{%invoices}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-billing_invoice_items-billing_invoice_id', '{{%billing_invoice_items}}');
        $this->dropForeignKey('fk-billing_invoice_items-invoice_id', '{{%billing_invoice_items}}');
        $this->dropTable('{{%billing_invoice_items}}');
    }
}