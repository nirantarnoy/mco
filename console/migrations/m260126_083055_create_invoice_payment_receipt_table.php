<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice_payment_receipt}}`.
 */
class m260126_083055_create_invoice_payment_receipt_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invoice_payment_receipt}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'payment_date' => $this->date()->notNull(),
            'amount' => $this->decimal(18, 2)->notNull(),
            'payment_method' => $this->string(255)->notNull(),
            'attachment' => $this->string(255),
            'company_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Create index for column `invoice_id`
        $this->createIndex(
            '{{%idx-invoice_payment_receipt-invoice_id}}',
            '{{%invoice_payment_receipt}}',
            'invoice_id'
        );

        // Add foreign key for table `{{%invoices}}`
        $this->addForeignKey(
            '{{%fk-invoice_payment_receipt-invoice_id}}',
            '{{%invoice_payment_receipt}}',
            'invoice_id',
            '{{%invoices}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drops foreign key for table `{{%invoices}}`
        $this->dropForeignKey(
            '{{%fk-invoice_payment_receipt-invoice_id}}',
            '{{%invoice_payment_receipt}}'
        );

        // Drops index for column `invoice_id`
        $this->dropIndex(
            '{{%idx-invoice_payment_receipt-invoice_id}}',
            '{{%invoice_payment_receipt}}'
        );

        $this->dropTable('{{%invoice_payment_receipt}}');
    }
}
