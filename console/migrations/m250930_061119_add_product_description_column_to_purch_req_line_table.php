<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_req_line}}`.
 */
class m250930_061119_add_product_description_column_to_purch_req_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_req_line}}', 'product_description', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_req_line}}', 'product_description');
    }
}
