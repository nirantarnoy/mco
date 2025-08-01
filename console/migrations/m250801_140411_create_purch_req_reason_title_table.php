_<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_req_reason}}`.
 */
class m250801_140411_create_purch_req_reason_title_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_req_reason_title}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->string(),
            'status' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch_req_reason_title}}');
    }
}
