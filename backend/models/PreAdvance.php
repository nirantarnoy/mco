<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "pre_advance".
 */
class PreAdvance extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 100;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pre_advance';
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
            [['vendor_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'company_id'], 'integer'],
            [['amount'], 'number'],
            [['pre_advance_no'], 'string', 'max' => 50],
            [['recipient_name'], 'string', 'max' => 255],
            [['remark'], 'string'],
            [['pre_advance_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pre_advance_no' => 'Pre-Advance No.',
            'trans_date' => 'Date',
            'recipient_name' => 'ชื่อผู้รับเงิน',
            'vendor_id' => 'Vendor',
            'amount' => 'Amount',
            'remark' => 'หมายเหตุ',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'company_id' => 'Company ID',
        ];
    }

    /**
     * Gets query for [[PreAdvanceLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreAdvanceLines()
    {
        return $this->hasMany(PreAdvanceLine::class, ['pre_advance_id' => 'id']);
    }

    /**
     * Gets query for [[PreAdvanceDocs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreAdvanceDocs()
    {
        return $this->hasMany(PreAdvanceDoc::class, ['pre_advance_id' => 'id']);
    }

    /**
     * Gets query for [[PreAdvanceRefs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreAdvanceRefs()
    {
        return $this->hasMany(PreAdvanceRef::class, ['pre_advance_id' => 'id']);
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
        $prefix = 'PA' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'pre_advance_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->pre_advance_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->pre_advance_no)) {
                $this->pre_advance_no = $this->generateVoucherNo();
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

    public static function getRecipientNameOptions()
    {
        return [
            'เงินบริษัทสำรองจ่าย SCB คุณสรกฤษณ์ ผู้ดูแล' => 'เงินบริษัทสำรองจ่าย SCB คุณสรกฤษณ์ ผู้ดูแล',
            'เงินบริษัทเงินสดย่อย KBANK คุณอดิศร ผู้ดูแล' => 'เงินบริษัทเงินสดย่อย KBANK คุณอดิศร ผู้ดูแล',
            'เงินสำรองส่วนตัวบัญชี BBL คุณสรกฤษณ์' => 'เงินสำรองส่วนตัวบัญชี BBL คุณสรกฤษณ์',
            'เงินสำรองส่วนตัวบัญชีคุณภภัช' => 'เงินสำรองส่วนตัวบัญชีคุณภภัช',
            'เงินสำรองส่วนตัวบัญชีคุณณัฏฐชัย' => 'เงินสำรองส่วนตัวบัญชีคุณณัฏฐชัย',
        ];
    }
}
