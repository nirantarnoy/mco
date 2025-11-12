<?php

namespace backend\models;

use Yii;
use yii\web\UploadedFile;
use backend\models\Paymentmethod;

/**
 * This is the model class for table "purch_payment_line".
 *
 * @property int $id
 * @property int|null $purch_payment_id
 * @property int|null $bank_id
 * @property string|null $bank_name
 * @property int|null $payment_method_id
 * @property float|null $pay_amount
 * @property string|null $doc
 * @property string|null $nodet
 *
 * @property PurchPayment $purchPayment
 * @property PaymentMethod $paymentMethod
 */
class PurchPaymentLine extends \yii\db\ActiveRecord
{
    public $doc_file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_payment_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_payment_id', 'bank_id', 'payment_method_id'], 'integer'],
            [['pay_amount'], 'number'],
            [['bank_name'], 'string', 'max' => 255],
            [['doc', 'nodet'], 'string', 'max' => 500],
            [['doc_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxSize' => 1024 * 1024 * 2],
            [['purch_payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchPayment::class, 'targetAttribute' => ['purch_payment_id' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::class, 'targetAttribute' => ['payment_method_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_payment_id' => 'การจ่ายเงิน',
            'bank_id' => 'ธนาคาร',
            'bank_name' => 'ชื่อธนาคาร',
            'payment_method_id' => 'ประเภทการโอน',
            'pay_amount' => 'จำนวนเงิน',
            'doc' => 'เอกสารแนบ',
            'doc_file' => 'อัพโหลดสลิป',
            'nodet' => 'หมายเหตุ',
        ];
    }

    /**
     * Gets query for [[PurchPayment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchPayment()
    {
        return $this->hasOne(PurchPayment::class, ['id' => 'purch_payment_id']);
    }

    /**
     * Gets query for [[PaymentMethod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(\backend\models\Paymentmethod::class, ['id' => 'payment_method_id']);
    }
}