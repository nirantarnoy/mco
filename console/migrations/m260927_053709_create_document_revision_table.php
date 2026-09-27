<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_revision}}`.
 */
class m260927_053709_create_document_revision_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document_revision}}', [
            'id' => $this->primaryKey(),
            'doc_type' => $this->string(20)->notNull()->comment('PO, NPR'),
            'doc_id' => $this->integer()->notNull(),
            'doc_no' => $this->string(50)->notNull(),
            'rev' => $this->integer()->notNull()->defaultValue(1),
            'data_json' => 'LONGTEXT',
            'note' => $this->text(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);

        $this->createIndex(
            'idx-document_revision-doc_type-doc_id',
            '{{%document_revision}}',
            ['doc_type', 'doc_id']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-document_revision-doc_type-doc_id',
            '{{%document_revision}}'
        );
        $this->dropTable('{{%document_revision}}');
    }
}
