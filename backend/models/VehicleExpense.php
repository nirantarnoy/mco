<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class VehicleExpense extends ActiveRecord
{
    public static function tableName()
    {
        return 'vehicle_expense';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['expense_date'], 'required'],
            [['expense_date', 'created_at', 'updated_at'], 'safe'],
            [['job_description'], 'string'],
            [['total_distance', 'vehicle_cost', 'total_wage'], 'number'],
            [['passenger_count'], 'integer'],
            [['job_no', 'import_batch'], 'string', 'max' => 50],
            [['vehicle_no'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expense_date' => 'วันที่ใช้งานรถ',
            'job_no' => 'เลขที่ใบงาน (Job No.)',
            'job_description' => 'รายละเอียดงาน',
            'vehicle_no' => 'ทะเบียนรถ',
            'total_distance' => 'ระยะทางรวม (กม.)',
            'vehicle_cost' => 'ค่าใช้จ่ายรถ (บาท)',
            'passenger_count' => 'จำนวนผู้ใช้รถ',
            'total_wage' => 'ค่าจ้างรวม (บาท)',
            'import_batch' => 'Batch',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    /**
     * คำนวณค่าใช้จ่ายรวมทั้งหมด
     */
    public function getTotalExpense()
    {
        return $this->vehicle_cost + $this->total_wage;
    }

    /**
     * ดึงค่าใช้จ่ายทั้งหมดของใบงาน
     */
    public static function getTotalExpenseByJobNo($jobNo)
    {
        $records = self::find()->where(['job_no' => $jobNo])->all();
        $total = 0;
        foreach ($records as $record) {
            $total += $record->getTotalExpense();
        }
        return $total;
    }

    /**
     * ดึงระยะทางรวมของใบงาน
     */
    public static function getTotalDistanceByJobNo($jobNo)
    {
        return self::find()
            ->where(['job_no' => $jobNo])
            ->sum('total_distance') ?? 0;
    }
}