<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%petty_cash_detail}}`.
 */
class m250920_090436_add_job_ref_id_column_to_petty_cash_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%petty_cash_detail}}', 'job_ref_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%petty_cash_detail}}', 'job_ref_id');
    }
}
