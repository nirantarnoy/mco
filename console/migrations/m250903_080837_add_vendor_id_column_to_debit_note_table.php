<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%debit_not}}`.
 */
class m250903_080837_add_vendor_id_column_to_debit_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%debit_note}}', 'vendor_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%debit_note}}', 'vendor_id');
    }
}
