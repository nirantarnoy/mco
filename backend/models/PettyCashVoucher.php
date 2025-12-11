<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "petty_cash_voucher".
 *
 * @property int $id
 * @property string $pcv_no
 * @property string $date
 * @property string $name
 * @property float $amount
 * @property string|null $paid_for
 * @property string|null $issued_by
 * @property string|null $issued_date
 * @property string|null $approved_by
 * @property string|null $approved_date
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PettyCashDetail[] $details
 */
class PettyCashVoucher extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'petty_cash_voucher';
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'amount'], 'required'],
            [['pcv_no','date', 'issued_date', 'approved_date','created_by','updated_by'], 'safe'],
            [['amount'], 'number', 'min' => 0],
            [['paid_for'], 'string'],
            [['status','quotation_id','customer_id','pay_for_emp_id','job_id','vendor_id','approve_status'], 'integer'],
            [['pcv_no'], 'string', 'max' => 50],
            [['name', 'issued_by', 'approved_by'], 'string', 'max' => 255],
            [['pcv_no'], 'unique'],
            [['name'],'default','value' => ''],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pcv_no' => 'PCV No.',
            'date' => 'วันที่',
            'name' => 'ชื่อผู้รับเงิน',
            'amount' => 'จำนวนเงิน',
            'paid_for' => 'จ่ายเพื่อ',
            'issued_by' => 'ผู้จัดทำ',
            'issued_date' => 'วันที่จัดทำ',
            'approved_by' => 'ผู้อนุมัติ',
            'approved_date' => 'วันที่อนุมัติ',
            'status' => 'สถานะ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขล่าสุด',
            'quotation_id' => 'ใบเสนอราคา',
            'pay_for_emp_id' => 'พนักงาน',
            'customer_id' => 'ลูกค้า',
            'job_id' => 'ใบงาน',
            'vendor_id' => 'ผู้จำหน่าย',
            'approve_status' => 'สถานะการอนุมัติ'
        ];
    }

    /**
     * Gets query for [[Details]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetails()
    {
        return $this->hasMany(PettyCashDetail::class, ['voucher_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * Generate PCV Number
     */
    public function generatePcvNo()
    {
        $date = $this->date ? strtotime($this->date) : time();
        $prefix = 'PCV' . date('y', $date) . str_pad(date('m', $date), 2, '0', STR_PAD_LEFT);
        $lastRecord = self::find()
            ->where(['like', 'pcv_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->pcv_no, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%03d', $newNumber);
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->pcv_no)) {
                $this->pcv_no = $this->generatePcvNo();
            }
            $this->company_id = \Yii::$app->session->get('company_id');
            $this->approved_by = \backend\models\User::findEmployeeNameByUserId(5);
            return true;
        }
        $this->company_id = 1;

        return false;
    }

    /**
     * Calculate total amount from details
     */
    public function calculateTotalAmount()
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->total;
        }
        return $total;
    }

    /**
     * Update amount from details
     */
    public function updateAmountFromDetails()
    {
        $this->amount = $this->calculateTotalAmount();
        return $this->save(false);
    }
}