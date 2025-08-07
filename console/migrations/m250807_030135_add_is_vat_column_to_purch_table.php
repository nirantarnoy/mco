<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m250807_030135_add_is_vat_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch}}', 'is_vat', $this->integer());
        $this->addColumn('{{%purch}}', 'vat_perccent', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'is_vat');
        $this->dropColumn('{{%purch}}', 'vat_perccent');
    }
}
