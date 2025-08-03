<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%credit_note_item}}`.
 */
class m250803_135418_add_product_id_column_to_credit_note_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%credit_note_item}}', 'product_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%credit_note_item}}', 'product_id');
    }
}
