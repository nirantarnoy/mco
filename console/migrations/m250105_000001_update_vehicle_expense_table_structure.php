<?php

use yii\db\Migration;

/**
 * Class m250105_000001_update_vehicle_expense_table_structure
 */
class m250105_000001_update_vehicle_expense_table_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // ลบคอลัมน์เก่าที่ไม่ใช้
        $this->dropColumn('{{%vehicle_expense}}', 'driver_name');
        $this->dropColumn('{{%vehicle_expense}}', 'distance_start');
        $this->dropColumn('{{%vehicle_expense}}', 'distance_end');
        $this->dropColumn('{{%vehicle_expense}}', 'quantity');
        $this->dropColumn('{{%vehicle_expense}}', 'is_summary');

        // เพิ่มคอลัมน์ใหม่
        $this->addColumn('{{%vehicle_expense}}', 'total_distance',
            $this->decimal(10, 2)->null()->defaultValue(0)->after('vehicle_no')->comment('ระยะทางรวม (กม.)')
        );

        $this->addColumn('{{%vehicle_expense}}', 'vehicle_cost',
            $this->decimal(10, 2)->null()->defaultValue(0)->after('total_distance')->comment('ค่าใช้จ่ายรถ (บาท)')
        );

        $this->addColumn('{{%vehicle_expense}}', 'passenger_count',
            $this->integer()->null()->defaultValue(0)->after('vehicle_cost')->comment('จำนวนผู้ใช้รถ')
        );

        $this->addColumn('{{%vehicle_expense}}', 'total_wage',
            $this->decimal(10, 2)->null()->defaultValue(0)->after('passenger_count')->comment('ค่าจ้างรวม (บาท)')
        );

        // เปลี่ยนชื่อคอลัมน์ amount เป็นชื่อที่ชัดเจนขึ้น (ถ้าต้องการ)
        // หรือใช้ vehicle_cost + total_wage แทน
        $this->dropColumn('{{%vehicle_expense}}', 'amount');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Rollback
        $this->addColumn('{{%vehicle_expense}}', 'amount',
            $this->decimal(10, 2)->notNull()->defaultValue(0)
        );

        $this->dropColumn('{{%vehicle_expense}}', 'total_wage');
        $this->dropColumn('{{%vehicle_expense}}', 'passenger_count');
        $this->dropColumn('{{%vehicle_expense}}', 'vehicle_cost');
        $this->dropColumn('{{%vehicle_expense}}', 'total_distance');

        $this->addColumn('{{%vehicle_expense}}', 'is_summary',
            $this->tinyInteger(1)->defaultValue(0)
        );
        $this->addColumn('{{%vehicle_expense}}', 'quantity',
            $this->integer()->null()->defaultValue(1)
        );
        $this->addColumn('{{%vehicle_expense}}', 'distance_end',
            $this->decimal(10, 2)->null()->defaultValue(0)
        );
        $this->addColumn('{{%vehicle_expense}}', 'distance_start',
            $this->decimal(10, 2)->null()->defaultValue(0)
        );
        $this->addColumn('{{%vehicle_expense}}', 'driver_name',
            $this->string(100)->null()
        );
    }
}