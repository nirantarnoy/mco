<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%stock_trans}}`.
 */
class m250710_163942_add_updated_at_column_to_stock_trans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%stock_trans}}', 'updated_at', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%stock_trans}}', 'updated_at');
    }
}
