<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_doc}}`.
 */
class m250831_130100_add_doc_type_id_column_to_purch_none_pr_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_none_pr_doc}}', 'doc_type_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_none_pr_doc}}', 'doc_type_id');
    }
}
