<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

class Wht extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 100;

    public static function tableName()
    {
        return 'wht';
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
            [['trans_date'], 'safe'],
            [['ref_id', 'vendor_id', 'wht_type', 'pay_condition', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'company_id'], 'integer'],
            [['base_amount', 'wht_percent', 'wht_amount'], 'number'],
            [['wht_no', 'ref_type'], 'string', 'max' => 50],
            [['wht_desc', 'other_desc'], 'string', 'max' => 255],
            [['wht_no'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wht_no' => 'เลขที่เอกสาร หัก ณ ที่จ่าย',
            'trans_date' => 'วันที่',
            'ref_type' => 'อ้างอิงเอกสาร',
            'ref_id' => 'Ref ID',
            'vendor_id' => 'ผู้ถูกหักภาษี (Vendor)',
            'wht_type' => 'ประเภท ภงด.',
            'pay_condition' => 'เงื่อนไขการหัก',
            'base_amount' => 'จำนวนเงิน',
            'wht_percent' => 'อัตราหัก (%)',
            'wht_amount' => 'ยอดหักภาษี',
            'wht_desc' => 'ประเภทเงินได้',
            'other_desc' => 'ระบุประเภทเงินได้อื่นๆ',
            'status' => 'สถานะ',
        ];
    }

    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    public function generateDocNo()
    {
        $prefix = 'WHT' . date('ym');
        $lastRecord = self::find()
            ->where(['like', 'wht_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->wht_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->wht_no)) {
                $this->wht_no = $this->generateDocNo();
            }
            if (!\Yii::$app->request->isConsoleRequest) {
                $this->company_id = (\Yii::$app->session->get('company_id') == 100 ? null : \Yii::$app->session->get('company_id')) ?: 1;
            } else {
                $this->company_id = 1;
            }
            return true;
        }
        return false;
    }
}
