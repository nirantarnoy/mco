<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class InvoicePaymentReceipt extends ActiveRecord
{
    public $file;

    public static function tableName()
    {
        return 'invoice_payment_receipt';
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
            [['invoice_id', 'payment_date', 'amount', 'payment_method'], 'required'],
            [['invoice_id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'company_id'], 'integer'],
            [['amount'], 'number'],
            [['payment_date'], 'safe'],
            [['payment_method', 'attachment', 'note', 'bank_account', 'cheque_number'], 'string', 'max' => 255],
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'payment_date' => 'วันที่รับเงิน',
            'amount' => 'ยอดเงิน',
            'payment_method' => 'ช่องทางการรับชำระ',
            'attachment' => 'เอกสารแนบ',
            'file' => 'แนบไฟล์เอกสาร',
            'note' => 'หมายเหตุ',
            'bank_account' => 'เลขที่บัญชีธนาคาร',
            'cheque_number' => 'เลขที่เช็ค',
        ];
    }

    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }
}