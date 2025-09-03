<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%credit_not}}`.
 */
class m250903_074107_add_vendor_id_column_to_credit_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%credit_note}}', 'vendor_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%credit_note}}', 'vendor_id');
    }
}
