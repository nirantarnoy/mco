<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%driver_wage_report}}`.
 */
class m260817_180000_create_driver_wage_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableNames = $this->db->getSchema()->getTableNames();
        if (!in_array('driver_wage_report', $tableNames)) {
            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            }

            $this->createTable('{{%driver_wage_report}}', [
                'id' => $this->primaryKey()->comment('ID'),
                'report_month' => $this->integer()->notNull()->comment('เดือน'),
                'report_year' => $this->integer()->notNull()->comment('ปี'),
                'vehicle_no' => $this->string(50)->notNull()->comment('ทะเบียนรถ'),
                'driver_name' => $this->string(255)->null()->comment('ชื่อพนักงานขับรถ'),
                'cost_of_living' => $this->decimal(12, 2)->defaultValue(0.00)->comment('ค่าครองชีพ'),
                'trip_allowance' => $this->decimal(12, 2)->defaultValue(0.00)->comment('ค่าเที่ยว'),
                'social_security' => $this->decimal(12, 2)->defaultValue(0.00)->comment('หักประกันสังคม'),
                'ot' => $this->decimal(12, 2)->defaultValue(0.00)->comment('โอที'),
                'food_allowance' => $this->decimal(12, 2)->defaultValue(0.00)->comment('เบี้ยเลี้ยง'),
                'tax_withholding' => $this->decimal(12, 2)->defaultValue(0.00)->comment('หักภาษี ภงด.'),
                'cash_advance' => $this->decimal(12, 2)->defaultValue(0.00)->comment('หักเงินยืมทดรอง'),
                'traffic_fine' => $this->decimal(12, 2)->defaultValue(0.00)->comment('หักค่าปรับจราจร'),
                'damage_insurance' => $this->decimal(12, 2)->defaultValue(0.00)->comment('หักประกันของเสีย'),
                'product_damage' => $this->decimal(12, 2)->defaultValue(0.00)->comment('หักสินค้าเสียหาย'),
                'other_deduction' => $this->decimal(12, 2)->defaultValue(0.00)->comment('หักอื่นๆ'),
                'net_total' => $this->decimal(12, 2)->defaultValue(0.00)->comment('คงเหลือสุทธิ'),
                'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP')->comment('วันที่สร้าง'),
                'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('วันที่แก้ไข'),
            ], $tableOptions);

            $this->createIndex(
                'idx-driver_wage_report-year_month_vehicle',
                '{{%driver_wage_report}}',
                ['report_year', 'report_month', 'vehicle_no'],
                true
            );

            $this->addCommentOnTable('{{%driver_wage_report}}', 'ตารางรายงานสรุปค่าแรงพนักงานขับรถ');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%driver_wage_report}}');
    }
}
