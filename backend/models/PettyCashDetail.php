<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "petty_cash_detail".
 *
 * @property int $id
 * @property int $voucher_id
 * @property string|null $ac_code
 * @property string|null $detail_date
 * @property string|null $detail
 * @property float $amount
 * @property float|null $vat
 * @property float|null $vat_amount
 * @property float|null $wht
 * @property float|null $other
 * @property float $total
 * @property int|null $sort_order
 *
 * @property PettyCashVoucher $voucher
 */
class PettyCashDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'petty_cash_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['voucher_id'], 'required'],
            [['voucher_id', 'sort_order'], 'integer'],
            [['detail_date','job_ref_id','other'], 'safe'],
            [['detail'], 'string'],
            [['amount', 'vat', 'vat_amount', 'wht', 'total','vat_prohibit'], 'number'],
            [['ac_code'], 'string', 'max' => 50],
            [['voucher_id'], 'exist', 'skipOnError' => true, 'targetClass' => PettyCashVoucher::class, 'targetAttribute' => ['voucher_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'voucher_id' => 'Voucher ID',
            'ac_code' => 'A/C CODE',
            'detail_date' => 'DATE',
            'detail' => 'DETAIL',
            'amount' => 'AMOUNT',
            'vat' => 'VAT',
            'vat_amount' => 'VAT จำนวน',
            'wht' => 'W/H',
            'other' => 'อื่นๆ',
            'total' => 'TOTAL',
            'sort_order' => 'Sort Order',
            'vat_prohibit' => 'VAT ต้องห้าม',
        ];
    }

    /**
     * Gets query for [[Voucher]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVoucher()
    {
        return $this->hasOne(PettyCashVoucher::class, ['id' => 'voucher_id']);
    }

    /**
     * Calculate total automatically
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Convert to float and handle empty values
            $amount = !empty($this->amount) ? (float)$this->amount : 0.00;
            $vat = !empty($this->vat) ? (float)$this->vat : 0.00;
            $vatProhibit = !empty($this->vat_prohibit) ? (float)$this->vat_prohibit : 0.00;
            $wht = !empty($this->wht) ? (float)$this->wht : 0.00;
            $other = !empty($this->other) ? (float)$this->other : 0.00;

            // Calculate total = amount + vat + vat_prohibit - wht + other
            $this->total = $amount + $vat + $vatProhibit - $wht + $other;

            // Ensure all numeric fields are properly set
            $this->amount = $amount;
            $this->vat = $vat;
            $this->vat_prohibit = $vatProhibit;
            $this->wht = $wht;
            $this->other = $other;

            // Convert detail_date from d/m/Y to Y-m-d
            if (!empty($this->detail_date) && strpos($this->detail_date, '/') !== false) {
                $dateParts = explode('/', $this->detail_date);
                if (count($dateParts) == 3) {
                    $this->detail_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }

            return true;
        }
        return false;
    }

    /**
     * After save, update voucher total
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->voucher) {
            $this->voucher->updateAmountFromDetails();
        }
    }

    /**
     * After delete, update voucher total
     */
    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->voucher) {
            $this->voucher->updateAmountFromDetails();
        }
    }
}