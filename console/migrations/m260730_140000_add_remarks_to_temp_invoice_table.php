<?php

use yii\db\Migration;

/**
 * Handles adding remarks column to table `{{%temp_invoice}}`.
 */
class m260730_140000_add_remarks_to_temp_invoice_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%temp_invoice}}', 'remarks', $this->text()->comment('หมายเหตุ/ข้อมูลเพิ่มเติมจาก OCR')->after('raw_text'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%temp_invoice}}', 'remarks');
    }
}
