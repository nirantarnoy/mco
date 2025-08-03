<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%credit_note_item}}`.
 */
class m250803_145531_add_unit_id_column_to_credit_note_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%credit_note_item}}', 'unit_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%credit_note_item}}', 'unit_id');
    }
}
