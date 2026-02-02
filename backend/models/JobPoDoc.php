<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "job_po_doc".
 *
 * @property int $id
 * @property int $job_id รหัสใบงาน
 * @property string $file_name ชื่อไฟล์
 * @property string $file_path ที่อยู่ไฟล์
 * @property int|null $file_size ขนาดไฟล์ (bytes)
 * @property int|null $uploaded_at วันที่อัปโหลด
 * @property int|null $uploaded_by ผู้อัปโหลด
 *
 * @property Job $job
 */
class JobPoDoc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'job_po_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'file_name', 'file_path'], 'required'],
            [['job_id', 'file_size', 'uploaded_at', 'uploaded_by'], 'integer'],
            [['file_name', 'file_path'], 'string', 'max' => 255],
            [['job_id'], 'exist', 'skipOnError' => true, 'targetClass' => Job::className(), 'targetAttribute' => ['job_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'file_name' => 'ชื่อไฟล์',
            'file_path' => 'ที่อยู่ไฟล์',
            'file_size' => 'ขนาดไฟล์',
            'uploaded_at' => 'วันที่อัปโหลด',
            'uploaded_by' => 'ผู้อัปโหลด',
        ];
    }

    /**
     * Gets query for [[Job]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::className(), ['id' => 'job_id']);
    }

    /**
     * ฟังก์ชันสำหรับลบไฟล์จากระบบ
     */
    public function deleteFile()
    {
        $filePath = Yii::getAlias('@backend/web/uploads/job/' . $this->file_path);
        if (file_exists($filePath)) {
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
