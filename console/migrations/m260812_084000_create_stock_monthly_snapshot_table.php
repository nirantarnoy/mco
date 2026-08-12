<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock_monthly_snapshot}}`.
 */
class m260812_084000_create_stock_monthly_snapshot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stock_monthly_snapshot}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'warehouse_id' => $this->integer()->notNull(),
            'lot_no' => $this->string(50)->null(),
            'qty' => $this->float()->defaultValue(0),
            'snapshot_period' => $this->string(7)->notNull()->comment('Format YYYY-MM'),
            'created_at' => $this->dateTime(),
        ]);

        $this->createIndex(
            '{{%idx-stock_monthly_snapshot-period}}',
            '{{%stock_monthly_snapshot}}',
            'snapshot_period'
        );
        $this->createIndex(
            '{{%idx-stock_monthly_snapshot-product_id}}',
            '{{%stock_monthly_snapshot}}',
            'product_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-stock_monthly_snapshot-product_id}}', '{{%stock_monthly_snapshot}}');
        $this->dropIndex('{{%idx-stock_monthly_snapshot-period}}', '{{%stock_monthly_snapshot}}');
        $this->dropTable('{{%stock_monthly_snapshot}}');
    }
}
