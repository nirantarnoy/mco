<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%vendor}}`.
 */
class m260730_132203_add_is_vat_column_to_vendor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if the column exists to prevent errors if already added manually
        $table = Yii::$app->db->schema->getTableSchema('{{%vendor}}');
        if (!isset($table->columns['is_vat'])) {
            $this->addColumn('{{%vendor}}', 'is_vat', $this->tinyInteger(1)->comment('1=VAT, 2=NO VAT'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%vendor}}', 'is_vat');
    }
}
