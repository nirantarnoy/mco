<?php

use yii\db\Migration;

/**
 * Class m260730_150000_insert_homepro_ocr_pattern
 */
class m260730_150000_insert_homepro_ocr_pattern extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%ocr_pattern}}', [
            'name' => 'บริษัท โฮม โปรดักส์ เซ็นเตอร์ จำกัด (มหาชน)',
            'tax_id' => '0107544000043',
            'regex_invoice_no' => '/(?:เลขที่)\s*[:.]?\s*([A-Z0-9\-\/]{4,20})/iu',
            'regex_date' => '/(?:วันที่)\s*[:.]?\s*(\d{2}\/\d{2}\/\d{4})/',
            'regex_total' => '/(?:มูลค่ารวม|มูลค่ารวม\s*\(.*?\))\s*[:.]?\s*([0-9,]+\.[0-9]{2})/iu',
            'regex_item_start' => '/^(\d{1,2})\s+([A-Z0-9\s]{4,25})\s+(.+)$/u',
            'parsing_strategy' => 'block',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%ocr_pattern}}', ['tax_id' => '0107544000043']);
    }
}
