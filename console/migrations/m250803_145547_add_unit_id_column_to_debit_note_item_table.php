<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%debit_note_item}}`.
 */
class m250803_145547_add_unit_id_column_to_debit_note_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%debit_note_item}}', 'unit_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%debit_note_item}}', 'unit_id');
    }
}
