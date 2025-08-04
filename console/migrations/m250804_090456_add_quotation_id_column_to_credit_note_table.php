<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%credit_note}}`.
 */
class m250804_090456_add_quotation_id_column_to_credit_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%credit_note}}', 'quotation_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%credit_note}}', 'quotation_id');
    }
}
