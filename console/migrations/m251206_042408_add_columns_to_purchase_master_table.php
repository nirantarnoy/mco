<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purchase_master}}`.
 */
class m251206_042408_add_columns_to_purchase_master_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purchase_master}}', 'department_id', $this->integer()->comment('แผนก'));
        $this->addColumn('{{%purchase_master}}', 'invoice_no', $this->string()->comment('เลขที่ใบกำกับ'));
        $this->addColumn('{{%purchase_master}}', 'vat_period', $this->string()->comment('ยื่นภาษีรวมในงวด'));
        $this->addColumn('{{%purchase_master}}', 'additional_note', $this->string()->comment('อื่นเพิ่มเติม'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purchase_master}}', 'department_id');
        $this->dropColumn('{{%purchase_master}}', 'invoice_no');
        $this->dropColumn('{{%purchase_master}}', 'vat_period');
        $this->dropColumn('{{%purchase_master}}', 'additional_note');
    }
}
