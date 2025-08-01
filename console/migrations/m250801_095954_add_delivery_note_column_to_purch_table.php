<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250801_095954_add_delivery_note_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'delivery_note', $this->string());
        $this->addColumn('{{%purch}}', 'payment_note', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'delivery_note');
        $this->dropColumn('{{%purch}}', 'payment_note');
    }
}
