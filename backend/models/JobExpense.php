<?php

namespace backend\models;

use Yii;

class JobExpense extends \yii\db\ActiveRecord
{
    public $expense_file; // สำหรับ upload file

    public static function tableName()
    {
        return 'job_expense';
    }

    public function rules()
    {
        return [
            [['job_id'], 'required'],
            [['job_id'], 'integer'],
            [['trans_date'], 'safe'],
            [['line_amount'], 'number'],
            [['description'], 'string'],
            [['line_doc'], 'string', 'max' => 255],
            [['expense_file'], 'file', 'skipOnEmpty' => true,
                'extensions' => 'jpg, jpeg, png, pdf, doc, docx',
                'maxSize' => 1024 * 1024 * 5], // 5MB
            [['job_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Job::className(), 'targetAttribute' => ['job_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'trans_date' => 'วันที่',
            'description' => 'รายการค่าใช้จ่าย',
            'line_amount' => 'จำนวนเงิน',
            'line_doc' => 'เอกสารแนบ',
            'expense_file' => 'เอกสารแนบ',
        ];
    }

    public function getJob()
    {
        return $this->hasOne(Job::className(), ['id' => 'job_id']);
    }
}