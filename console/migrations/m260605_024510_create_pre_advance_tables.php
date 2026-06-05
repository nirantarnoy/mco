<?php

use yii\db\Migration;

/**
 * Class m260605_024510_create_pre_advance_tables
 */
class m260605_024510_create_pre_advance_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. pre_advance table
        $this->createTable('{{%pre_advance}}', [
            'id' => $this->primaryKey(),
            'vendor_id' => $this->integer()->null(),
            'pre_advance_no' => $this->string(50)->null()->unique(),
            'trans_date' => $this->date()->null(),
            'recipient_name' => $this->string(255)->null(),
            'amount' => $this->decimal(10, 2)->null()->defaultValue(0),
            'remark' => $this->text()->null(),
            'status' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'company_id' => $this->integer()->null(),
        ]);

        // 2. pre_advance_line table
        $this->createTable('{{%pre_advance_line}}', [
            'id' => $this->primaryKey(),
            'pre_advance_id' => $this->integer()->null(),
            'line_date' => $this->date()->null(),
            'description' => $this->string(255)->null(),
            'amount' => $this->decimal(10, 2)->null()->defaultValue(0),
            'remark' => $this->text()->null(),
        ]);

        // 3. pre_advance_doc table
        $this->createTable('{{%pre_advance_doc}}', [
            'id' => $this->primaryKey(),
            'pre_advance_id' => $this->integer()->null(),
            'file_name' => $this->string(255)->null(),
            'file_path' => $this->string(255)->null(),
            'file_size' => $this->integer()->null(),
            'uploaded_at' => $this->integer()->null(),
            'uploaded_by' => $this->integer()->null(),
        ]);

        // 4. pre_advance_ref table
        $this->createTable('{{%pre_advance_ref}}', [
            'id' => $this->primaryKey(),
            'pre_advance_id' => $this->integer()->null(),
            'ref_id' => $this->integer()->null(),
            'ref_type' => $this->integer()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%pre_advance_ref}}');
        $this->dropTable('{{%pre_advance_doc}}');
        $this->dropTable('{{%pre_advance_line}}');
        $this->dropTable('{{%pre_advance}}');
    }
}
