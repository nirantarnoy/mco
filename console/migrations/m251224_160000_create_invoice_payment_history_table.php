<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice_payment_history}}`.
 */
class m251224_160000_create_invoice_payment_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invoice_payment_history}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull()->comment('Invoice ID (Source)'),
            'receipt_id' => $this->integer()->notNull()->comment('Receipt ID (Payment)'),
            'amount' => $this->decimal(10, 2)->notNull()->comment('Payment Amount'),
            'payment_date' => $this->date()->notNull(),
            'note' => $this->string()->null(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->null(),
            'company_id' => $this->integer()->null(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-invoice_payment_history-invoice_id',
            '{{%invoice_payment_history}}',
            'invoice_id',
            '{{%invoices}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-invoice_payment_history-receipt_id',
            '{{%invoice_payment_history}}',
            'receipt_id',
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
        $this->dropForeignKey('fk-invoice_payment_history-invoice_id', '{{%invoice_payment_history}}');
        $this->dropForeignKey('fk-invoice_payment_history-receipt_id', '{{%invoice_payment_history}}');
        $this->dropTable('{{%invoice_payment_history}}');
    }
}
