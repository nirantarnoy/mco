<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_doc_complete}}`.
 */
class m251116_032903_create_job_doc_complete_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_doc_complete}}', [
            'id' => $this->primaryKey(),
            'job_id' => $this->integer(),
            'purch_req_doc' => $this->integer(),
            'purch_doc' => $this->integer(),
            'quotation_doc' => $this->integer(),
            'bill_placement_doc' => $this->integer(),
            'invoice_doc' => $this->integer(),
            'debit_doc' => $this->integer(),
            'tax_invoice_doc' => $this->integer(),
            'purch_receive_doc' => $this->integer(),
            'bill_receipt_doc' => $this->integer(),
            'bill_payment_doc' => $this->integer(),
            'product_issue_doc' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%job_doc_complete}}');
    }
}
