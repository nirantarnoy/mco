<?php
use yii\db\Migration;

class m240101_000004_create_billing_invoices_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%billing_invoices}}', [
            'id' => $this->primaryKey(),
            'billing_number' => $this->string(50)->notNull()->unique(),
            'billing_date' => $this->date()->notNull(),
            'customer_id' => $this->integer()->notNull(),
            'subtotal' => $this->decimal(15, 2)->defaultValue(0.00),
            'discount_percent' => $this->decimal(5, 2)->defaultValue(0.00),
            'discount_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'vat_percent' => $this->decimal(5, 2)->defaultValue(7.00),
            'vat_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'total_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'payment_due_date' => $this->date(),
            'credit_terms' => $this->integer(3)->defaultValue(30),
            'notes' => $this->text(),
            'status' => "ENUM('draft','issued','paid','cancelled') DEFAULT 'issued'",
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Add indexes
        $this->createIndex('idx-billing_invoices-customer_id', '{{%billing_invoices}}', 'customer_id');
        $this->createIndex('idx-billing_invoices-billing_date', '{{%billing_invoices}}', 'billing_date');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-billing_invoices-customer_id', '{{%billing_invoices}}');
        $this->dropTable('{{%billing_invoices}}');
    }
}