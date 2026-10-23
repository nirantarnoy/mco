<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_company}}` and `{{%user_module_permission}}`.
 */
class m261023_083000_create_user_company_and_module_permission_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // 1. ตาราง user_company (ความสัมพันธ์ผู้ใช้งานกับบริษัท)
        if ($this->db->getTableSchema('user_company') === null) {
            $this->createTable('{{%user_company}}', [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()->notNull(),
                'company_id' => $this->integer()->notNull(),
                'status' => $this->integer()->defaultValue(1),
                'created_at' => $this->integer()->null(),
                'created_by' => $this->integer()->null(),
            ], $tableOptions);

            $this->createIndex(
                'idx-user_company-user_id-company_id',
                '{{%user_company}}',
                ['user_id', 'company_id'],
                true
            );
        }

        // 2. ตาราง user_module_permission (ตารางเก็บค่า Matrix สิทธิ์รายบริษัท)
        if ($this->db->getTableSchema('user_module_permission') === null) {
            $this->createTable('{{%user_module_permission}}', [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()->notNull(),
                'company_id' => $this->integer()->notNull(),
                'module_key' => $this->string(64)->notNull(),
                'permission_type' => $this->string(10)->defaultValue('x'),
                'created_at' => $this->integer()->null(),
                'updated_at' => $this->integer()->null(),
                'updated_by' => $this->integer()->null(),
            ], $tableOptions);

            $this->createIndex(
                'idx-user_module_perm-user_comp_mod',
                '{{%user_module_permission}}',
                ['user_id', 'company_id', 'module_key'],
                true
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_module_permission}}');
        $this->dropTable('{{%user_company}}');
    }
}
