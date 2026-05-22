<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "driver_wage_report".
 *
 * @property int $id
 * @property int $report_month
 * @property int $report_year
 * @property string $vehicle_no
 * @property string|null $driver_name
 * @property float|null $cost_of_living
 * @property float|null $trip_allowance
 * @property float|null $social_security
 * @property float|null $ot
 * @property float|null $food_allowance
 * @property float|null $tax_withholding
 * @property float|null $cash_advance
 * @property float|null $traffic_fine
 * @property float|null $damage_insurance
 * @property float|null $product_damage
 * @property float|null $other_deduction
 * @property float|null $net_total
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class DriverWageReport extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'driver_wage_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['report_month', 'report_year', 'vehicle_no'], 'required'],
            [['report_month', 'report_year'], 'integer'],
            [
                [
                    'cost_of_living', 'trip_allowance', 'social_security', 'ot', 
                    'food_allowance', 'tax_withholding', 'cash_advance', 'traffic_fine', 
                    'damage_insurance', 'product_damage', 'other_deduction', 'net_total'
                ], 
                'number'
            ],
            [['created_at', 'updated_at'], 'safe'],
            [['vehicle_no'], 'string', 'max' => 50],
            [['driver_name'], 'string', 'max' => 255],
            [['report_year', 'report_month', 'vehicle_no'], 'unique', 'targetAttribute' => ['report_year', 'report_month', 'vehicle_no']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'report_month' => 'เดือน',
            'report_year' => 'ปี',
            'vehicle_no' => 'ทะเบียนรถ',
            'driver_name' => 'ชื่อพนักงานขับรถ',
            'cost_of_living' => 'ค่าครองชีพ',
            'trip_allowance' => 'ค่าเที่ยว',
            'social_security' => 'หักประกันสังคม',
            'ot' => 'โอที',
            'food_allowance' => 'เบี้ยเลี้ยง',
            'tax_withholding' => 'หักภาษี ภงด.',
            'cash_advance' => 'หักเงินยืมทดรอง',
            'traffic_fine' => 'หักค่าปรับจราจร',
            'damage_insurance' => 'หักประกันของเสีย',
            'product_damage' => 'หักสินค้าเสียหาย',
            'other_deduction' => 'หักอื่นๆ',
            'net_total' => 'คงเหลือสุทธิ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }
}
