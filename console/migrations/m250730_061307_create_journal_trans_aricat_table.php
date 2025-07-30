<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%journal_trans_aricat}}`.
 */
class m250730_061307_create_journal_trans_aricat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%journal_trans_aricat}}', [
            'id' => $this->primaryKey(),
            'journal_trans_id' => $this->integer(),
            'worker_id' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%journal_trans_aricat}}');
    }
}
