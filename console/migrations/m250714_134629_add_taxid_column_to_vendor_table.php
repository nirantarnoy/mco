<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%vendor}}`.
 */
class m250714_134629_add_taxid_column_to_vendor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%vendor}}', 'taxid', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%vendor}}', 'taxid');
    }
}
