<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%quotation_line}}`.
 */
class m250710_144350_create_quotation_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%quotation_line}}', [
            'id' => $this->primaryKey(),
            'quotation_id' => $this->integer(),
            'product_id' => $this->integer(),
            'product_name' => $this->string(),
            'qty' => $this->float(),
            'line_price' => $this->float(),
            'line_total' => $this->float(),
            'discount_amount' => $this->float(),
            'status' => $this->integer(),
            'note' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%quotation_line}}');
    }
}
