<?php

//=============================================================================

/**
 * PaymentAttachment Model
 */

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * PaymentAttachment Model
 *
 * @property int $id
 * @property int $payment_receipt_id
 * @property string $file_name
 * @property string $original_name
 * @property string $file_path
 * @property int $file_size
 * @property string $file_type
 * @property string $mime_type
 * @property int $uploaded_by
 * @property string $created_at
 */
class PaymentAttachment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_attachments';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false, // ไม่มี updated_at ในตารางนี้
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'uploaded_by',
                'updatedByAttribute' => false, // ไม่มี updated_by ในตารางนี้
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_receipt_id', 'file_name', 'original_name', 'file_path', 'file_size', 'file_type', 'uploaded_by'], 'required'],
            [['payment_receipt_id', 'file_size', 'uploaded_by'], 'integer'],
            [['created_at'], 'safe'],
            [['file_name', 'original_name'], 'string', 'max' => 255],
            [['file_path'], 'string', 'max' => 500],
            [['file_type', 'mime_type'], 'string', 'max' => 100],
            [['payment_receipt_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentReceipt::class, 'targetAttribute' => ['payment_receipt_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_receipt_id' => 'ใบเสร็จรับเงิน',
            'file_name' => 'ชื่อไฟล์',
            'original_name' => 'ชื่อไฟล์เดิม',
            'file_path' => 'ที่อยู่ไฟล์',
            'file_size' => 'ขนาดไฟล์',
            'file_type' => 'ประเภทไฟล์',
            'mime_type' => 'MIME Type',
            'uploaded_by' => 'ผู้อัพโหลด',
            'created_at' => 'วันที่อัพโหลด',
        ];
    }

    /**
     * Gets query for [[PaymentReceipt]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentReceipt()
    {
        return $this->hasOne(PaymentReceipt::class, ['id' => 'payment_receipt_id']);
    }

    /**
     * Gets query for [[UploadedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedBy()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'uploaded_by']);
    }

    /**
     * Get file size in human readable format
     */
    public function getFormattedFileSize()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file icon based on file type
     */
    public function getFileIcon()
    {
        $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));

        $icons = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'jpg' => 'fas fa-file-image text-warning',
            'jpeg' => 'fas fa-file-image text-warning',
            'png' => 'fas fa-file-image text-warning',
            'gif' => 'fas fa-file-image text-warning',
        ];

        return $icons[$extension] ?? 'fas fa-file text-secondary';
    }

    /**
     * Check if file exists
     */
    public function fileExists()
    {
        $fullPath = Yii::getAlias('@backend/web/') . $this->file_path;
        return file_exists($fullPath);
    }

    /**
     * Get download URL
     */
    public function getDownloadUrl()
    {
        return \yii\helpers\Url::to(['/payment-receipt/download-attachment', 'id' => $this->id]);
    }

    /**
     * Delete file from filesystem
     */
    public function deleteFile()
    {
        $fullPath = Yii::getAlias('@backend/web/') . $this->file_path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    /**
     * Before delete - remove file from filesystem
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            return $this->deleteFile();
        }
        return false;
    }
}