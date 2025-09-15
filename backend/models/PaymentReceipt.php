<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\UploadedFile;

/**
 * PaymentReceipt Model
 *
 * @property int $id
 * @property string $receipt_number
 * @property int $billing_invoice_id
 * @property int $job_id
 * @property string $payment_date
 * @property string $payment_method
 * @property string $bank_name
 * @property string $account_number
 * @property string $cheque_number
 * @property string $cheque_date
 * @property float $received_amount
 * @property float $discount_amount
 * @property float $vat_amount
 * @property float $withholding_tax
 * @property float $net_amount
 * @property float $remaining_balance
 * @property string $payment_status
 * @property string $attachment_path
 * @property string $attachment_name
 * @property string $notes
 * @property int $received_by
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 */
class PaymentReceipt extends ActiveRecord
{
    public $attachment_file;

    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_CHEQUE = 'cheque';
    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_OTHER = 'other';

    const STATUS_PARTIAL = 'partial';
    const STATUS_FULL = 'full';
    const STATUS_OVERPAID = 'overpaid';

    public static function tableName()
    {
        return 'payment_receipts';
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
            [['receipt_number', 'billing_invoice_id', 'payment_date', 'received_amount', 'net_amount', 'received_by'], 'required'],
            [['billing_invoice_id', 'job_id', 'received_by', 'created_by', 'updated_by', 'status'], 'integer'],
            [['payment_date', 'cheque_date', 'created_at', 'updated_at'], 'safe'],
            [['payment_method', 'payment_status'], 'string'],
            [['received_amount', 'discount_amount', 'vat_amount', 'withholding_tax', 'net_amount', 'remaining_balance'], 'number'],
            [['notes'], 'string'],
            [['receipt_number'], 'string', 'max' => 50],
            [['bank_name'], 'string', 'max' => 100],
            [['account_number', 'cheque_number'], 'string', 'max' => 50],
            [['attachment_path'], 'string', 'max' => 500],
            [['attachment_name'], 'string', 'max' => 255],
            [['receipt_number'], 'unique'],
            [['payment_method'], 'in', 'range' => [
                self::PAYMENT_METHOD_CASH,
                self::PAYMENT_METHOD_BANK_TRANSFER,
                self::PAYMENT_METHOD_CHEQUE,
                self::PAYMENT_METHOD_CREDIT_CARD,
                self::PAYMENT_METHOD_OTHER
            ]],
            [['payment_status'], 'in', 'range' => [
                self::STATUS_PARTIAL,
                self::STATUS_FULL,
                self::STATUS_OVERPAID
            ]],
            [['attachment_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, jpg, jpeg, png, doc, docx, xls, xlsx'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'receipt_number' => 'เลขที่ใบเสร็จ',
            'billing_invoice_id' => 'ใบแจ้งหนี้',
            'job_id' => 'รหัสงาน',
            'payment_date' => 'วันที่รับเงิน',
            'payment_method' => 'วิธีการชำระ',
            'bank_name' => 'ชื่อธนาคาร',
            'account_number' => 'เลขที่บัญชี',
            'cheque_number' => 'เลขที่เช็ค',
            'cheque_date' => 'วันที่เช็ค',
            'received_amount' => 'จำนวนเงินที่รับ',
            'discount_amount' => 'ส่วนลด',
            'vat_amount' => 'ภาษีมูลค่าเพิ่ม',
            'withholding_tax' => 'ภาษีหัก ณ ที่จ่าย',
            'net_amount' => 'จำนวนเงินสุทธิ',
            'remaining_balance' => 'ยอดคงเหลือ',
            'payment_status' => 'สถานะการชำระ',
            'attachment_path' => 'ไฟล์แนบ',
            'attachment_name' => 'ชื่อไฟล์แนบ',
            'attachment_file' => 'แนบไฟล์หลักฐาน',
            'notes' => 'หมายเหตุ',
            'received_by' => 'ผู้รับเงิน',
            'created_by' => 'สร้างโดย',
            'updated_by' => 'แก้ไขโดย',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'status' => 'สถานะ',
        ];
    }

    // Relations
    public function getBillingInvoice()
    {
        return $this->hasOne(BillingInvoice::class, ['id' => 'billing_invoice_id']);
    }

    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }

    public function getReceivedBy()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'received_by']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'updated_by']);
    }

    public function getPaymentReceiptDetails()
    {
        return $this->hasMany(PaymentReceiptDetail::class, ['payment_receipt_id' => 'id']);
    }

    public function getPaymentAttachments()
    {
        return $this->hasMany(PaymentAttachment::class, ['payment_receipt_id' => 'id']);
    }

    // Static methods for dropdowns
    public static function getPaymentMethods()
    {
        return [
            self::PAYMENT_METHOD_CASH => 'เงินสด',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'โอนเงิน',
            self::PAYMENT_METHOD_CHEQUE => 'เช็ค',
            self::PAYMENT_METHOD_CREDIT_CARD => 'บัตรเครดิต',
            self::PAYMENT_METHOD_OTHER => 'อื่นๆ',
        ];
    }

    public static function getPaymentStatuses()
    {
        return [
            self::STATUS_PARTIAL => 'รับบางส่วน',
            self::STATUS_FULL => 'รับครบ',
            self::STATUS_OVERPAID => 'รับเกิน',
        ];
    }

    // Generate receipt number
    public function generateReceiptNumber()
    {
        $prefix = 'RC' . date('Ym');
        $lastReceipt = self::find()
            ->where(['like', 'receipt_number', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Handle file upload
    public function upload()
    {
        if ($this->attachment_file) {
            $uploadPath = Yii::getAlias('@backend/web/uploads/payment-receipts/');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = $this->receipt_number . '_' . time() . '.' . $this->attachment_file->extension;
            $filePath = $uploadPath . $fileName;

            if ($this->attachment_file->saveAs($filePath)) {
                $this->attachment_path = 'uploads/payment-receipts/' . $fileName;
                $this->attachment_name = $this->attachment_file->name;
                return true;
            }
        }
        return true;
    }

    // Before save
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->receipt_number)) {
                $this->receipt_number = $this->generateReceiptNumber();
            }

            // Calculate net amount
            $this->net_amount = $this->received_amount - $this->discount_amount - $this->withholding_tax;

            // Update remaining balance
            if ($this->billingInvoice) {
                $totalPaid = self::find()
                    ->where(['billing_invoice_id' => $this->billing_invoice_id])
                    ->andWhere(['!=', 'id', $this->id])
                    ->sum('net_amount') ?? 0;

                $this->remaining_balance = $this->billingInvoice->total_amount - ($totalPaid + $this->net_amount);

                // Update payment status
                if ($this->remaining_balance <= 0) {
                    $this->payment_status = self::STATUS_FULL;
                } elseif ($this->remaining_balance < $this->billingInvoice->total_amount) {
                    $this->payment_status = self::STATUS_PARTIAL;
                }
            }

            return true;
        }
        return false;
    }

    // After save
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Update billing invoice status
        if ($this->billingInvoice) {
            $totalPaid = self::find()
                ->where(['billing_invoice_id' => $this->billing_invoice_id])
                ->sum('net_amount') ?? 0;

            if ($totalPaid >= $this->billingInvoice->total_amount) {
                $this->billingInvoice->updateAttributes(['status' => 'paid']);
            } else {
                $this->billingInvoice->updateAttributes(['status' => 'partial_paid']);
            }
        }
    }
}