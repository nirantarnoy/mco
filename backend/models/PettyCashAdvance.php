<?php
namespace backend\models;

use backend\models\PettyCashVoucher;
use backend\models\SystemSetting;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

class PettyCashAdvance extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    public static function getMaxAmount()
    {
        return (float) SystemSetting::getValue('petty_cash_max_amount', 30000);
    }

    public static function getMinAmount()
    {
        return (float) SystemSetting::getValue('petty_cash_min_amount', 3000);
    }

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
            [['request_date', 'recall_at'], 'safe'],
            [['purpose', 'remarks', 'recall_reason'], 'string'],
            [['employee_id', 'approved_by', 'is_recall', 'recall_by'], 'integer'],
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
            'is_recall' => 'สถานะขอ Recall',
            'recall_reason' => 'เหตุผลการขอ Recall',
            'recall_by' => 'ผู้ขอ Recall',
            'recall_at' => 'เวลาที่ขอ Recall',
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

    public static function generateAdvanceNo($request_date)
    {
        $year = date('Y',strtotime($request_date));
        $month = date('m',strtotime($request_date));
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
            ->where(['status' => ['approved', 'paid','pending']])
            ->sum('amount') ?? 0;

        $totalUsed = PettyCashVoucher::find()
            ->sum('amount') ?? 0;

        return $totalAdvance - $totalUsed;
    }

    public static function canRequestAdvance($amount)
    {
        $currentBalance = static::getCurrentBalance();
        return ($currentBalance + $amount) <= static::getMaxAmount();
    }

    public static function needsRefill()
    {
        return static::getCurrentBalance() <= static::getMinAmount();
    }

    public function beforeSave($insert){
        if ($this->request_date) {
            $date = \DateTime::createFromFormat('m/d/Y', $this->request_date);
            if ($date) {
                $this->request_date = $date->format('Y-m-d');
            } else {
                $this->request_date = date('Y-m-d', strtotime($this->request_date));
            }
        }
        $this->created_at = time();
        $this->created_by = Yii::$app->user->id;
        $this->company_id = (\Yii::$app->session->get('company_id') == 100 ? null : \Yii::$app->session->get('company_id'));
        return true;
    }

    public function getRecallUser()
    {
        return $this->hasOne(\backend\models\User::class, ['id' => 'recall_by']);
    }

    public static function ensureRecallColumns()
    {
        try {
            $db = Yii::$app->db;
            $table = $db->getTableSchema(self::tableName(), true);
            if ($table) {
                if (!isset($table->columns['is_recall'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'is_recall', 'INT(11) DEFAULT 0')->execute();
                }
                if (!isset($table->columns['recall_reason'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'recall_reason', 'TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL')->execute();
                } else {
                    $db->createCommand("ALTER TABLE " . self::tableName() . " MODIFY `recall_reason` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL")->execute();
                }
                if (!isset($table->columns['recall_by'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'recall_by', 'INT(11) NULL')->execute();
                }
                if (!isset($table->columns['recall_at'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'recall_at', 'DATETIME NULL')->execute();
                }
                $db->getSchema()->refreshTableSchema(self::tableName());
            }
        } catch (\Exception $e) {
            Yii::error('Failed to ensure recall columns for petty_cash_advance: ' . $e->getMessage());
        }
    }
}