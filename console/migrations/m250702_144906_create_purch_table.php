<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch}}`.
 */
class m250702_144906_create_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch}}', [
            'id' => $this->primaryKey(),
            'purch_no' => $this->string(),
            'purch_date' => $this->datetime(),
            'vendor_id' => $this->integer(),
            'vendor_name' => $this->string(),
            'status' => $this->integer(),
            'note' => $this->string(),
            'approve_status' => $this->integer(),
            'total_amount' => $this->float(),
            'total_text' => $this->string(),
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
        $this->dropTable('{{%purch}}');
    }
}
