<?php

use yii\db\Migration;

class m260410_075737_add_custom_fields_to_ocr_pattern extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ocr_pattern}}', 'regex_item_start', $this->string(500)->after('regex_total'));
        $this->addColumn('{{%ocr_pattern}}', 'parsing_strategy', $this->string(50)->after('regex_item_start'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ocr_pattern}}', 'regex_item_start');
        $this->dropColumn('{{%ocr_pattern}}', 'parsing_strategy');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260410_075737_add_custom_fields_to_ocr_pattern cannot be reverted.\n";

        return false;
    }
    */
}
