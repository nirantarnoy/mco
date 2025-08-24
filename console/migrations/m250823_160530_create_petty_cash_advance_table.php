<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%petty_cash_advance}}`.
 */
class m250823_160530_create_petty_cash_advance_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('petty_cash_advance', [
            'id' => $this->primaryKey(),
            'advance_no' => $this->string(50)->notNull()->unique(),
            'request_date' => $this->date()->notNull(),
            'employee_id' => $this->integer(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'purpose' => $this->text()->notNull(),
            'status' => $this->string(20)->defaultValue('pending'),
            'approved_by' => $this->integer(),
            'remarks' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-petty_cash_advance-employee_id',
            'petty_cash_advance',
            'employee_id',
            'employee',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-petty_cash_advance-approved_by',
            'petty_cash_advance',
            'approved_by',
            'employee',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx-petty_cash_advance-status',
            'petty_cash_advance',
            'status'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-petty_cash_advance-employee_id', 'petty_cash_advance');
        $this->dropForeignKey('fk-petty_cash_advance-approved_by', 'petty_cash_advance');
        $this->dropTable('petty_cash_advance');
    }
}
