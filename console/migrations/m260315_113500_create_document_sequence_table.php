<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_sequence}}`.
 */
class m260315_113500_create_document_sequence_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // https://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%document_sequence}}', [
            'id' => $this->primaryKey(),
            'document_type' => $this->string(50)->notNull()->unique(),
            'prefix' => $this->string(50),
            'last_year' => $this->integer(),
            'last_number' => $this->integer(),
        ], $tableOptions);

        // Insert initial records
        $this->insert('{{%document_sequence}}', [
            'document_type' => 'credit_note',
            'prefix' => 'CN',
            'last_year' => (int)date('Y'),
            'last_number' => 0,
        ]);

        $this->insert('{{%document_sequence}}', [
            'document_type' => 'debit_note',
            'prefix' => 'DB',
            'last_year' => (int)date('Y'),
            'last_number' => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%document_sequence}}');
    }
}
