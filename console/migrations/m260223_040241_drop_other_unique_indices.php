<?php

use yii\db\Migration;

class m260223_040241_drop_other_unique_indices extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('billing_number', 'billing_invoices');
    }

    public function safeDown()
    {
        $this->createIndex('billing_number', 'billing_invoices', 'billing_number', true);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260223_040241_drop_other_unique_indices cannot be reverted.\n";

        return false;
    }
    */
}
