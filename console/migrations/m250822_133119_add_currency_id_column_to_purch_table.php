<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250822_133119_add_currency_id_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'currency_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'currency_id');
    }
}
