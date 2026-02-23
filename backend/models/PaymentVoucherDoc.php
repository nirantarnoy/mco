<?php

namespace backend\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "payment_voucher_doc".
 *
 * @property int $id
 * @property int $payment_voucher_id
 * @property string|null $file_name
 * @property string|null $file_path
 * @property int|null $file_size
 * @property int|null $uploaded_at
 * @property int|null $uploaded_by
 *
 * @property PaymentVoucher $paymentVoucher
 */
class PaymentVoucherDoc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_voucher_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_voucher_id'], 'required'],
            [['payment_voucher_id', 'file_size', 'uploaded_at', 'uploaded_by'], 'integer'],
            [['file_name', 'file_path'], 'string', 'max' => 255],
            [['payment_voucher_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentVoucher::class, 'targetAttribute' => ['payment_voucher_id' => 'id']],
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
            'file_name' => 'ชื่อไฟล์',
            'file_path' => 'ที่อยู่ไฟล์',
            'file_size' => 'ขนาดไฟล์',
            'uploaded_at' => 'วันที่อัปโหลด',
            'uploaded_by' => 'ผู้อัปโหลด',
        ];
    }

    /**
     * Gets query for [[PaymentVoucher]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentVoucher()
    {
        return $this->hasOne(PaymentVoucher::class, ['id' => 'payment_voucher_id']);
    }

    /**
     * ลบไฟล์จากระบบ
     */
    public function deleteFile()
    {
        $filePath = Yii::getAlias('@backend/web/uploads/payment_voucher/' . $this->file_path);
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteFile();
            return true;
        }
        return false;
    }
}
