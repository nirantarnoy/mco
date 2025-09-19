<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_req_line}}`.
 */
class m250919_124310_add_doc_ref_no_column_to_purch_req_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_req_line}}', 'doc_ref_no', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_req_line}}', 'doc_ref_no');
    }
}
