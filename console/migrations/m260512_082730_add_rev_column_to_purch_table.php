<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch}}`.
 */
class m260512_082730_add_rev_column_to_purch_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if column already exists to avoid errors
        $table = $this->db->getTableSchema('{{%purch}}');
        if (!isset($table->columns['rev'])) {
            $this->addColumn('{{%purch}}', 'rev', $this->integer()->defaultValue(0)->after('net_amount'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch}}', 'rev');
    }
}
