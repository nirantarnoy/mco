<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_table}}`.
 */
class m250713_053957_add_job_id_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'job_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'job_id');
    }
}
