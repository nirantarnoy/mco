<?php
namespace backend\models;

use backend\models\PettyCashVoucher;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

class PettyCashAdvance extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    const MAX_AMOUNT = 30000;
    const MIN_AMOUNT = 3000;

    public static function tableName()
    {
        return 'petty_cash_advance';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['request_date', 'amount', 'purpose'], 'required'],
            [['amount'], 'number', 'min' => 1],
            [['request_date'], 'safe'],
            [['purpose', 'remarks'], 'string'],
            [['employee_id', 'approved_by'], 'integer'],
            [['status'], 'string', 'max' => 20],
            [['advance_no'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'advance_no' => 'เลขที่ใบเบิกทดแทน',
            'request_date' => 'วันที่เบิก',
            'employee_id' => 'พนักงานที่เบิก',
            'amount' => 'จำนวนเงิน',
            'purpose' => 'วัตถุประสงค์',
            'status' => 'สถานะ',
            'approved_by' => 'ผู้อนุมัติ',
            'remarks' => 'หมายเหตุ',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['id' => 'employee_id']);
    }

    public function getApprover()
    {
        return $this->hasOne(Employee::class, ['id' => 'approved_by']);
    }

    public static function generateAdvanceNo()
    {
        $year = date('Y');
        $month = date('m');
        $lastNo = static::find()
            ->where(['like', 'advance_no', "ADV-{$year}{$month}"])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastNo) {
            $lastNumber = (int)substr($lastNo->advance_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "ADV-{$year}{$month}-{$newNumber}";
    }

    public static function getCurrentBalance()
    {
        // คำนวณยอดคงเหลือปัจจุบัน
        $totalAdvance = static::find()
            ->where(['status' => ['approved', 'paid']])
            ->sum('amount') ?? 0;

        $totalUsed = PettyCashVoucher::find()
            ->sum('amount') ?? 0;

        return $totalAdvance - $totalUsed;
    }

    public static function canRequestAdvance($amount)
    {
        $currentBalance = static::getCurrentBalance();
        return ($currentBalance + $amount) <= static::MAX_AMOUNT;
    }

    public static function needsRefill()
    {
        return static::getCurrentBalance() <= static::MIN_AMOUNT;
    }

    public function beforeSave($insert){
        $this->company_id = \Yii::$app->session->get('company_id');
        return true;
    }
}