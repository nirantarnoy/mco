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
            [['pcv_no', 'date', 'name', 'amount'], 'required'],
            [['date', 'issued_date', 'approved_date'], 'safe'],
            [['amount'], 'number', 'min' => 0],
            [['paid_for'], 'string'],
            [['status'], 'integer'],
            [['pcv_no'], 'string', 'max' => 50],
            [['name', 'issued_by', 'approved_by'], 'string', 'max' => 255],
            [['pcv_no'], 'unique'],
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
        $year = date('Y');
        $sequence = Yii::$app->db->createCommand()
            ->select(['last_number'])
            ->from('petty_cash_sequence')
            ->where(['year' => $year])
            ->queryOne();

        if (!$sequence) {
            // Insert new year
            Yii::$app->db->createCommand()
                ->insert('petty_cash_sequence', ['year' => $year, 'last_number' => 1])
                ->execute();
            $nextNumber = 1;
        } else {
            $nextNumber = $sequence['last_number'] + 1;
            Yii::$app->db->createCommand()
                ->update('petty_cash_sequence',
                    ['last_number' => $nextNumber],
                    ['year' => $year])
                ->execute();
        }

        return 'PCV' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
            return true;
        }
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