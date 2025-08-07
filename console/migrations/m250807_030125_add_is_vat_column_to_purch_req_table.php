<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_req}}`.
 */
class m250807_030125_add_is_vat_column_to_purch_req_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_req}}', 'is_vat', $this->integer());
        $this->addColumn('{{%purch_req}}', 'vat_perccent', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_req}}', 'is_vat');
        $this->dropColumn('{{%purch_req}}', 'vat_perccent');
    }
}
