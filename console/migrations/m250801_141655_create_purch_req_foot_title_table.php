<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_req_foot_title}}`.
 */
class m250801_141655_create_purch_req_foot_title_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_req_foot_title}}', [
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
        $this->dropTable('{{%purch_req_foot_title}}');
    }
}
