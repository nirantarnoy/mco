<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250814_041416_add_whd_tax_per_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'whd_tax_per', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'whd_tax_per');
    }
}
