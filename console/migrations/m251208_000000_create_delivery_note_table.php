<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%delivery_note}}`.
 */
class m251208_000000_create_delivery_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%delivery_note}}', [
            'id' => $this->primaryKey(),
            'dn_no' => $this->string(),
            'date' => $this->date(),
            'job_id' => $this->integer(),
            'customer_id' => $this->integer(),
            'customer_name' => $this->string(),
            'address' => $this->text(),
            'attn' => $this->string(),
            'our_ref' => $this->string(),
            'from_name' => $this->string(),
            'tel' => $this->string(),
            'ref_no' => $this->string(),
            'page_no' => $this->string(),
            'status' => $this->integer()->defaultValue(0),
            'company_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->createTable('{{%delivery_note_line}}', [
            'id' => $this->primaryKey(),
            'delivery_note_id' => $this->integer(),
            'item_no' => $this->string(50),
            'description' => $this->text(),
            'part_no' => $this->string(),
            'qty' => $this->decimal(10, 2),
            'unit_id' => $this->integer(),
            'remark' => $this->text(),
        ]);
        
        // Add foreign key for table `delivery_note`
        $this->addForeignKey(
            'fk-delivery_note-job_id',
            '{{%delivery_note}}',
            'job_id',
            '{{%job}}',
            'id',
            'CASCADE'
        );

        // Add foreign key for table `delivery_note_line`
        $this->addForeignKey(
            'fk-delivery_note_line-delivery_note_id',
            '{{%delivery_note_line}}',
            'delivery_note_id',
            '{{%delivery_note}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-delivery_note_line-delivery_note_id', '{{%delivery_note_line}}');
        $this->dropForeignKey('fk-delivery_note-job_id', '{{%delivery_note}}');
        
        $this->dropTable('{{%delivery_note_line}}');
        $this->dropTable('{{%delivery_note}}');
    }
}
