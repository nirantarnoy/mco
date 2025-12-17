<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice_relations}}`.
 */
class m251217_161200_create_invoice_relations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invoice_relations}}', [
            'id' => $this->primaryKey(),
            'parent_invoice_id' => $this->integer()->notNull()->comment('ID ของเอกสารต้นฉบับ'),
            'child_invoice_id' => $this->integer()->notNull()->comment('ID ของเอกสารที่ copy มา'),
            'relation_type' => $this->string(50)->notNull()->comment('ประเภทความสัมพันธ์'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('วันที่สร้าง'),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-invoice_relations-parent_invoice_id',
            '{{%invoice_relations}}',
            'parent_invoice_id',
            '{{%invoices}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-invoice_relations-child_invoice_id',
            '{{%invoice_relations}}',
            'child_invoice_id',
            '{{%invoices}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes
        $this->createIndex(
            'idx-invoice_relations-parent_invoice_id',
            '{{%invoice_relations}}',
            'parent_invoice_id'
        );

        $this->createIndex(
            'idx-invoice_relations-child_invoice_id',
            '{{%invoice_relations}}',
            'child_invoice_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-invoice_relations-parent_invoice_id', '{{%invoice_relations}}');
        $this->dropForeignKey('fk-invoice_relations-child_invoice_id', '{{%invoice_relations}}');
        $this->dropIndex('idx-invoice_relations-parent_invoice_id', '{{%invoice_relations}}');
        $this->dropIndex('idx-invoice_relations-child_invoice_id', '{{%invoice_relations}}');
        $this->dropTable('{{%invoice_relations}}');
    }
}
