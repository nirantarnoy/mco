<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%invoices}}`.
 */
class m250821_101352_add_is_billed_column_to_invoices_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoices}}', 'is_billed', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoices}}', 'is_billed');
    }
}
