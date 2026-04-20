<?php

use yii\db\Migration;

class m260410_071605_add_product_code_to_temp_invoice_line extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%temp_invoice_line}}', 'product_code', $this->string(50)->after('temp_invoice_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%temp_invoice_line}}', 'product_code');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260410_071605_add_product_code_to_temp_invoice_line cannot be reverted.\n";

        return false;
    }
    */
}
