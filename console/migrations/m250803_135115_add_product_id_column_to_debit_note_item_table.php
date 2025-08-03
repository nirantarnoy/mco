<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%debit_note}}`.
 */
class m250803_135115_add_product_id_column_to_debit_note_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%debit_note_item}}', 'product_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%debit_note_item}}', 'product_id');
    }
}
