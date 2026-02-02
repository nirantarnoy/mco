<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "payment_voucher_ref".
 *
 * @property int $id
 * @property int $payment_voucher_id รหัส Payment Voucher
 * @property int $ref_type ประเภท: 1=PR, 2=PO
 * @property int $ref_id รหัสอ้างอิง PR/PO
 * @property string|null $ref_no เลขที่เอกสารอ้างอิง
 * @property float|null $amount จำนวนเงินที่จ่าย
 * @property int|null $created_at วันที่สร้าง
 *
 * @property PaymentVoucher $paymentVoucher
 */
class PaymentVoucherRef extends \yii\db\ActiveRecord
{
    const REF_TYPE_PR = 1;
    const REF_TYPE_PO = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_voucher_ref';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_voucher_id', 'ref_type', 'ref_id'], 'required'],
            [['payment_voucher_id', 'ref_type', 'ref_id', 'created_at'], 'integer'],
            [['amount'], 'number'],
            [['ref_no'], 'string', 'max' => 100],
            [['payment_voucher_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentVoucher::className(), 'targetAttribute' => ['payment_voucher_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_voucher_id' => 'Payment Voucher ID',
            'ref_type' => 'Ref Type',
            'ref_id' => 'Ref ID',
            'ref_no' => 'Ref No',
            'amount' => 'Amount',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[PaymentVoucher]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentVoucher()
    {
        return $this->hasOne(PaymentVoucher::className(), ['id' => 'payment_voucher_id']);
    }

    /**
     * ดึงข้อมูล PR
     */
    public function getPurchReq()
    {
        return $this->hasOne(PurchReq::className(), ['id' => 'ref_id'])->andWhere(['ref_type' => self::REF_TYPE_PR]);
    }

    /**
     * ดึงข้อมูล PO
     */
    public function getPurch()
    {
        return $this->hasOne(Purch::className(), ['id' => 'ref_id'])->andWhere(['ref_type' => self::REF_TYPE_PO]);
    }
}
