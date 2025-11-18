<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_vendor_bill}}`.
 */
class m251118_143250_create_purch_vendor_bill_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_vendor_bill}}', [
            'id' => $this->primaryKey(),
            'purch_id' => $this->integer(),
            'bill_date' => $this->datetime(),
            'appoinment_date' => $this->datetime(),
            'bill_doc' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch_vendor_bill}}');
    }
}
