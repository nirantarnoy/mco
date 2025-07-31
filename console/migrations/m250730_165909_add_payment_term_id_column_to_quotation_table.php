<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%quotation}}`.
 */
class m250730_165909_add_payment_term_id_column_to_quotation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%quotation}}', 'payment_term_id', $this->integer());
        $this->addColumn('{{%quotation}}', 'payment_method_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%quotation}}', 'payment_term_id');
        $this->dropColumn('{{%quotation}}', 'payment_method_id');
    }
}
