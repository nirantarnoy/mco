<?php

use yii\db\Migration;

class m260201_054645_create_payment_voucher_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->getTableSchema('{{%payment_voucher}}', true) === null) {
            $this->createTable('{{%payment_voucher}}', [
                'id' => $this->primaryKey(),
                'voucher_no' => $this->string(50)->unique(),
                'trans_date' => $this->date(),
                'recipient_id' => $this->integer(),
                'recipient_name' => $this->string(255),
                'payment_method' => $this->integer(),
                'cheque_no' => $this->string(50),
                'cheque_date' => $this->date(),
                'amount' => $this->decimal(18, 2),
                'paid_for' => $this->string(255),
                'ref_id' => $this->integer(),
                'ref_type' => $this->integer(),
                'status' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'created_by' => $this->integer(),
                'updated_by' => $this->integer(),
                'company_id' => $this->integer(),
            ], $tableOptions);
        }

        if ($this->db->getTableSchema('{{%payment_voucher_line}}', true) === null) {
            $this->createTable('{{%payment_voucher_line}}', [
                'id' => $this->primaryKey(),
                'payment_voucher_id' => $this->integer(),
                'account_code' => $this->string(50),
                'bill_code' => $this->string(50),
                'description' => $this->text(),
                'debit' => $this->decimal(18, 2),
                'credit' => $this->decimal(18, 2),
            ], $tableOptions);

            $this->addForeignKey(
                'fk-payment_voucher_line-payment_voucher_id',
                '{{%payment_voucher_line}}',
                'payment_voucher_id',
                '{{%payment_voucher}}',
                'id',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-payment_voucher_line-payment_voucher_id', '{{%payment_voucher_line}}');
        $this->dropTable('{{%payment_voucher_line}}');
        $this->dropTable('{{%payment_voucher}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260201_054645_create_payment_voucher_tables cannot be reverted.\n";

        return false;
    }
    */
}
