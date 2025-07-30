<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%payment_term}}`.
 */
class m240510_011624_add_day_count_column_to_payment_term_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%payment_term}}', 'day_count', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%payment_term}}', 'day_count');
    }
}
