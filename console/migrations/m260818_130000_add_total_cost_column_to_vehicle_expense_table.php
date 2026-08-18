<?php

use yii\db\Migration;

/**
 * Handles adding total_cost to table `{{%vehicle_expense}}`.
 */
class m260818_130000_add_total_cost_column_to_vehicle_expense_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%vehicle_expense}}',
            'total_cost',
            $this->decimal(10, 2)->null()->defaultValue(0)->comment('ราคารวม/ค่าใช้จ่ายรวม (บาท)')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%vehicle_expense}}', 'total_cost');
    }
}
