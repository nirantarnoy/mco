<?php

use yii\db\Migration;

/**
 * Handles adding bank account columns to table `{{%vendor}}`.
 */
class m260829_120000_add_bank_account_columns_to_vendor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%vendor}}');

        if (!isset($table->columns['account_num'])) {
            $this->addColumn('{{%vendor}}', 'account_num', $this->string(255)->null()->comment('เลขที่บัญชีธนาคาร'));
        }

        if (!isset($table->columns['bank_name'])) {
            $this->addColumn('{{%vendor}}', 'bank_name', $this->string(255)->null()->comment('ชื่อธนาคาร'));
        }

        if (!isset($table->columns['account_name'])) {
            $this->addColumn('{{%vendor}}', 'account_name', $this->string(255)->null()->comment('ชื่อบัญชีธนาคาร'));
        }

        if (!isset($table->columns['bank_account_file'])) {
            $this->addColumn('{{%vendor}}', 'bank_account_file', $this->string(255)->null()->comment('ไฟล์หน้าบัญชีธนาคาร'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%vendor}}');

        if (isset($table->columns['account_num'])) {
            $this->dropColumn('{{%vendor}}', 'account_num');
        }

        if (isset($table->columns['bank_name'])) {
            $this->dropColumn('{{%vendor}}', 'bank_name');
        }

        if (isset($table->columns['account_name'])) {
            $this->dropColumn('{{%vendor}}', 'account_name');
        }

        if (isset($table->columns['bank_account_file'])) {
            $this->dropColumn('{{%vendor}}', 'bank_account_file');
        }
    }
}
