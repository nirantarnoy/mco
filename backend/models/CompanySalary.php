<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "company_salary".
 *
 * @property int $id
 * @property int $company_id
 * @property int $salary_month
 * @property int $salary_year
 * @property float $amount
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 */
class CompanySalary extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_salary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'salary_month', 'salary_year'], 'required'],
            [['company_id', 'salary_month', 'salary_year'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['note'], 'string', 'max' => 255],
            [['company_id', 'salary_year', 'salary_month'], 'unique', 'targetAttribute' => ['company_id', 'salary_year', 'salary_month']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'บริษัท',
            'salary_month' => 'เดือน',
            'salary_year' => 'ปี',
            'amount' => 'จำนวนเงินเดือน (บาท)',
            'note' => 'หมายเหตุ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Helper: Fetch salary amount for a specific company, month, year
     */
    public static function getSalaryAmount($companyId, $month, $year)
    {
        $model = self::findOne([
            'company_id' => $companyId,
            'salary_month' => $month,
            'salary_year' => $year,
        ]);
        return $model ? floatval($model->amount) : 0.00;
    }
}
