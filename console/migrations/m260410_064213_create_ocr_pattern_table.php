<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ocr_pattern}}`.
 */
class m260410_064213_create_ocr_pattern_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ocr_pattern}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'tax_id' => $this->string(20)->notNull(),
            'regex_invoice_no' => $this->string(500),
            'regex_date' => $this->string(500),
            'regex_total' => $this->string(500),
            'status' => $this->smallInteger()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ocr_pattern}}');
    }
}
