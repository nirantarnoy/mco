<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%quotation}}`.
 */
class m250710_143929_create_quotation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%quotation}}', [
            'id' => $this->primaryKey(),
            'quotation_no' => $this->string(),
            'quotation_date' => $this->datetime(),
            'customer_id' => $this->integer(),
            'customer_name' => $this->string(),
            'status' => $this->integer(),
            'approve_status' => $this->integer(),
            'approve_by' => $this->integer(),
            'total_amount' => $this->float(),
            'total_amount_text' => $this->string(),
            'note' => $this->string(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%quotation}}');
    }
}
