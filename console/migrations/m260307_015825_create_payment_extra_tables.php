<?php

use yii\db\Migration;

class m260307_015825_create_payment_extra_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payment_extra_option}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->createTable('{{%invoice_payment_extra}}', [
            'id' => $this->primaryKey(),
            'payment_receipt_id' => $this->integer()->notNull(),
            'extra_option_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(18, 2)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('idx-invoice_payment_extra-receipt_id', '{{%invoice_payment_extra}}', 'payment_receipt_id');
        $this->createIndex('idx-invoice_payment_extra-option_id', '{{%invoice_payment_extra}}', 'extra_option_id');

        $this->addForeignKey(
            'fk-invoice_payment_extra-receipt_id',
            '{{%invoice_payment_extra}}',
            'payment_receipt_id',
            '{{%invoice_payment_receipt}}',
            'id',
            'CASCADE'
        );

        // Seed initial options
        $options = [
            'ค่าธรรมเนียมการโอน',
            'ส่วนลดรับ',
            'ภาษีถูกหัก ณ ที่จ่าย',
            'กำไรจากอัตราแลกเปลี่ยน',
            'ค่าปรับ',
            'ขาดทุนจากอัตราแลกเปลี่ยน',
            'ส่วนลดจ่าย',
            'ค่าใช้จ่ายอื่นๆ',
            'รายได้อื่นๆ',
        ];

        foreach ($options as $option) {
            $this->insert('{{%payment_extra_option}}', [
                'name' => $option,
                'status' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-invoice_payment_extra-receipt_id', '{{%invoice_payment_extra}}');
        $this->dropTable('{{%invoice_payment_extra}}');
        $this->dropTable('{{%payment_extra_option}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260307_015825_create_payment_extra_tables cannot be reverted.\n";

        return false;
    }
    */
}
