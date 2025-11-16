<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%job_doc_compete}}`.
 */
class m251116_045812_add_credit_doc_column_to_job_doc_complete_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%job_doc_complete}}', 'credit_doc', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%job_doc_compete}}', 'credit_doc');
    }
}
