<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment_voucher_doc}}`.
 */
class m260223_122333_create_payment_voucher_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payment_voucher_doc}}', [
            'id' => $this->primaryKey(),
            'payment_voucher_id' => $this->integer()->notNull(),
            'file_name' => $this->string(),
            'file_path' => $this->string(),
            'file_size' => $this->integer(),
            'uploaded_at' => $this->integer(),
            'uploaded_by' => $this->integer(),
        ]);

        $this->addForeignKey('fk-payment_voucher_doc-payment_voucher_id', '{{%payment_voucher_doc}}', 'payment_voucher_id', '{{%payment_voucher}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%payment_voucher_doc}}');
    }
}
