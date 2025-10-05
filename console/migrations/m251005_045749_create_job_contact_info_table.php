<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_contact_info}}`.
 */
class m251005_045749_create_job_contact_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_contact_info}}', [
            'id' => $this->primaryKey(),
            'job_id' => $this->integer(),
            'name' => $this->string(),
            'description' => $this->string(),
            'phone' => $this->string(),
            'email' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%job_contact_info}}');
    }
}
