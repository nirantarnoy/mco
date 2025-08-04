<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%debit_note}}`.
 */
class m250804_090447_add_quotation_id_column_to_debit_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%debit_note}}', 'quotation_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%debit_note}}', 'quotation_id');
    }
}
