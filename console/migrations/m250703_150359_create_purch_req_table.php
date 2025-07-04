<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_req}}`.
 */
class m250703_150359_create_purch_req_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_req}}', [
            'id' => $this->primaryKey(),
            'purch_req_no' => $this->string(),
            'purch_req_date' => $this->datetime(),
            'vendor_id' => $this->integer(),
            'vendor_name' => $this->string(),
            'status' => $this->integer(),
            'note' => $this->string(),
            'approve_status' => $this->integer(),
            'total_amount' => $this->float(),
            'total_text' => $this->string(),
            'purch_id' => $this->integer(),
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
        $this->dropTable('{{%purch_req}}');
    }
}
