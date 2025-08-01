<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_req}}`.
 */
class m250722_045940_add_approve_by_column_to_purch_req_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_req}}', 'approve_by', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_req}}', 'approve_by');
    }
}
