<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%job}}`.
 */
class m251005_045604_add_cus_po_no_column_to_job_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%job}}', 'cus_po_no', $this->string());
        $this->addColumn('{{%job}}', 'cus_po_date', $this->datetime());
        $this->addColumn('{{%job}}', 'cus_po_doc', $this->string());
        $this->addColumn('{{%job}}', 'summary_note', $this->string());
        $this->addColumn('{{%job}}', 'jsa_doc', $this->string());
        $this->addColumn('{{%job}}', 'report_doc', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%job}}', 'cus_po_no');
        $this->dropColumn('{{%job}}', 'cus_po_date');
        $this->dropColumn('{{%job}}', 'cus_po_doc');
        $this->dropColumn('{{%job}}', 'summary_note');
        $this->dropColumn('{{%job}}', 'jsa_doc');
        $this->dropColumn('{{%job}}', 'report_doc');
    }
}
