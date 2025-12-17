<?php

use yii\db\Migration;

/**
 * Class m251217_131800_add_note_column_to_delivery_note_table
 */
class m251217_131800_add_note_column_to_delivery_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%delivery_note}}', 'note', $this->text()->after('ref_no'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%delivery_note}}', 'note');
    }
}
