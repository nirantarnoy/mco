<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_req_line}}`.
 */
class m250703_150437_create_purch_req_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_req_line}}', [
            'id' => $this->primaryKey(),
            'purch_req_id' => $this->integer(),
            'product_id' => $this->integer(),
            'product_name' => $this->string(),
            'product_type' => $this->integer(),
            'qty' => $this->float(),
            'line_price' => $this->float(),
            'line_total' => $this->float(),
            'status' => $this->integer(),
            'note' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch_req_line}}');
    }
}
