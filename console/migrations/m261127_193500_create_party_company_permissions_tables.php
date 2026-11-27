<?php

use yii\db\Migration;

/**
 * Handles the creation of tables `customer_company`, `product_company`, `vendor_company`
 * and adds `is_common` to product and vendor tables.
 */
class m261127_193500_create_party_company_permissions_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 1. Add is_common column to product table if not exists
        $productTable = Yii::$app->db->getTableSchema('{{%product}}');
        if (!isset($productTable->columns['is_common'])) {
            $this->addColumn('{{%product}}', 'is_common', $this->integer()->defaultValue(0)->after('company_id'));
        }

        // 2. Add is_common column to vendor table if not exists
        $vendorTable = Yii::$app->db->getTableSchema('{{%vendor}}');
        if (!isset($vendorTable->columns['is_common'])) {
            $this->addColumn('{{%vendor}}', 'is_common', $this->integer()->defaultValue(0)->after('company_id'));
        }

        // 3. Create customer_company table
        if (Yii::$app->db->getTableSchema('{{%customer_company}}') === null) {
            $this->createTable('{{%customer_company}}', [
                'id' => $this->primaryKey(),
                'customer_id' => $this->integer()->notNull(),
                'company_id' => $this->integer()->notNull(),
                'created_at' => $this->integer()->null(),
            ], $tableOptions);

            $this->createIndex('idx-customer_company-customer_id', '{{%customer_company}}', 'customer_id');
            $this->createIndex('idx-customer_company-company_id', '{{%customer_company}}', 'company_id');
            $this->createIndex('idx-customer_company-unique', '{{%customer_company}}', ['customer_id', 'company_id'], true);
        }

        // 4. Create product_company table
        if (Yii::$app->db->getTableSchema('{{%product_company}}') === null) {
            $this->createTable('{{%product_company}}', [
                'id' => $this->primaryKey(),
                'product_id' => $this->integer()->notNull(),
                'company_id' => $this->integer()->notNull(),
                'created_at' => $this->integer()->null(),
            ], $tableOptions);

            $this->createIndex('idx-product_company-product_id', '{{%product_company}}', 'product_id');
            $this->createIndex('idx-product_company-company_id', '{{%product_company}}', 'company_id');
            $this->createIndex('idx-product_company-unique', '{{%product_company}}', ['product_id', 'company_id'], true);
        }

        // 5. Create vendor_company table
        if (Yii::$app->db->getTableSchema('{{%vendor_company}}') === null) {
            $this->createTable('{{%vendor_company}}', [
                'id' => $this->primaryKey(),
                'vendor_id' => $this->integer()->notNull(),
                'company_id' => $this->integer()->notNull(),
                'created_at' => $this->integer()->null(),
            ], $tableOptions);

            $this->createIndex('idx-vendor_company-vendor_id', '{{%vendor_company}}', 'vendor_id');
            $this->createIndex('idx-vendor_company-company_id', '{{%vendor_company}}', 'company_id');
            $this->createIndex('idx-vendor_company-unique', '{{%vendor_company}}', ['vendor_id', 'company_id'], true);
        }

        // 6. Migrate existing data:
        // Customer: if is_common = 0 and company_id is valid, insert into customer_company
        $now = time();
        $this->execute("
            INSERT IGNORE INTO {{%customer_company}} (customer_id, company_id, created_at)
            SELECT id, company_id, {$now}
            FROM {{%customer}}
            WHERE company_id IS NOT NULL AND company_id > 0 AND (is_common = 0 OR is_common IS NULL)
        ");

        // Product: insert existing company_id into product_company
        $this->execute("
            INSERT IGNORE INTO {{%product_company}} (product_id, company_id, created_at)
            SELECT id, company_id, {$now}
            FROM {{%product}}
            WHERE company_id IS NOT NULL AND company_id > 0
        ");

        // Vendor: insert existing company_id into vendor_company
        $this->execute("
            INSERT IGNORE INTO {{%vendor_company}} (vendor_id, company_id, created_at)
            SELECT id, company_id, {$now}
            FROM {{%vendor}}
            WHERE company_id IS NOT NULL AND company_id > 0
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vendor_company}}');
        $this->dropTable('{{%product_company}}');
        $this->dropTable('{{%customer_company}}');

        $vendorTable = Yii::$app->db->getTableSchema('{{%vendor}}');
        if (isset($vendorTable->columns['is_common'])) {
            $this->dropColumn('{{%vendor}}', 'is_common');
        }

        $productTable = Yii::$app->db->getTableSchema('{{%product}}');
        if (isset($productTable->columns['is_common'])) {
            $this->dropColumn('{{%product}}', 'is_common');
        }
    }
}
