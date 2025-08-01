<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purch_req_reason}}`.
 */
class m250801_141450_create_purch_req_reason_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purch_req_reason}}', [
            'id' => $this->primaryKey(),
            'purch_req_id' => $this->integer(),
            'reason_id' => $this->integer(),
            'is_enable' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%purch_req_reason}}');
    }
}
