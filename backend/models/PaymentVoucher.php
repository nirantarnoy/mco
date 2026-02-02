<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "payment_voucher".
 *
 * @property int $id
 * @property string|null $voucher_no
 * @property string|null $trans_date
 * @property int|null $recipient_id
 * @property string|null $recipient_name
 * @property int|null $payment_method
 * @property string|null $cheque_no
 * @property string|null $cheque_date
 * @property float|null $amount
 * @property string|null $paid_for
 * @property int|null $ref_id
 * @property int|null $ref_type
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $company_id
 *
 * @property PaymentVoucherLine[] $paymentVoucherLines
 */
class PaymentVoucher extends ActiveRecord
{
    const REF_TYPE_PR = 1;
    const REF_TYPE_PO = 2;
    const REF_TYPE_QT = 3;

    const PAY_METHOD_CASH = 1;
    const PAY_METHOD_CHEQUE = 2;

    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 100;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_voucher';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['trans_date'], 'safe'],
            [['recipient_id', 'vendor_id', 'payment_method', 'ref_id', 'ref_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'company_id'], 'integer'],
            [['amount'], 'number'],
            [['voucher_no', 'cheque_no'], 'string', 'max' => 50],
            [['recipient_name', 'paid_for'], 'string', 'max' => 255],
            [['cheque_date'], 'safe'],
            [['voucher_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'voucher_no' => 'PV No.',
            'trans_date' => 'Date',
            'recipient_id' => 'Recipient',
            'recipient_name' => 'Name',
            'vendor_id' => 'Vendor',
            'payment_method' => 'By',
            'cheque_no' => 'Cheque No.',
            'cheque_date' => 'วันที่หน้าเช็ค',
            'amount' => 'Amount',
            'paid_for' => 'Paid For/PR/PO/QT',
            'ref_id' => 'Ref ID',
            'ref_type' => 'Ref Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'company_id' => 'Company ID',
        ];
    }

    /**
     * Gets query for [[PaymentVoucherLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentVoucherLines()
    {
        return $this->hasMany(PaymentVoucherLine::class, ['payment_voucher_id' => 'id']);
    }

    /**
     * Gets query for [[PaymentVoucherRefs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentVoucherRefs()
    {
        return $this->hasMany(PaymentVoucherRef::class, ['payment_voucher_id' => 'id']);
    }

    /**
     * Gets query for [[Vendor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    /**
     * Generate Voucher Number
     */
    public function generateVoucherNo()
    {
        $prefix = 'PV' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'voucher_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->voucher_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->voucher_no)) {
                $this->voucher_no = $this->generateVoucherNo();
            }
            if (!\Yii::$app->request->isConsoleRequest) {
                $this->company_id = \Yii::$app->session->get('company_id') ?: 1;
            } else {
                $this->company_id = 1;
            }
            return true;
        }
        return false;
    }

    public static function getPaymentMethodOptions()
    {
        return [
            self::PAY_METHOD_CASH => 'Cash/TR/TT',
            self::PAY_METHOD_CHEQUE => 'Bank of Cheque',
        ];
    }
}
