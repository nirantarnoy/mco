<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_req_foot}}`.
 */
class m250801_141733_create_purch_req_foot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_req_foot}}', [
            'id' => $this->primaryKey(),
            'purch_req_id' => $this->integer(),
            'footer_id' => $this->integer(),
            'is_enable' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch_req_foot}}');
    }
}
