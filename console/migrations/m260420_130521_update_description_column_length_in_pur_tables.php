<?php

use yii\db\Migration;

/**
 * Class m260420_130521_update_description_column_length_in_pur_tables
 */
class m260420_130521_update_description_column_length_in_pur_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%purch_req_line}}', 'product_description', $this->text());
        $this->alterColumn('{{%purch_line}}', 'product_description', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%purch_req_line}}', 'product_description', $this->string(255));
        $this->alterColumn('{{%purch_line}}', 'product_description', $this->string(255));
    }
}
