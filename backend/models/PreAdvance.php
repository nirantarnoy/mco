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
            [['trans_date', 'checked_at', 'approved_at'], 'safe'],
            [['vendor_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'company_id', 'checked_by', 'approved_by'], 'integer'],
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
            'checked_by' => 'Checked By',
            'checked_at' => 'Checked At',
            'approved_by' => 'Approved By',
            'approved_at' => 'Approved At',
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

    /**
     * Get document sequence data for timeline
     */
    public function getDocumentSequenceData()
    {
        $steps = [];
        // 1. สร้างใบเบิก
        $steps[] = ['name' => 'สร้างใบเบิก', 'status' => 1, 'date' => $this->created_at ? date('d/m/Y H:i', $this->created_at) : ''];
        
        // 2. ตรวจสอบ
        if ($this->checked_at) {
            $steps[] = ['name' => 'ตรวจสอบแล้ว', 'status' => 1, 'date' => date('d/m/Y H:i', strtotime($this->checked_at))];
        } else {
            $steps[] = ['name' => 'รอตรวจสอบ', 'status' => 0, 'date' => ''];
        }

        // 3. อนุมัติ
        if ($this->approved_at) {
            $steps[] = ['name' => 'อนุมัติแล้ว', 'status' => 1, 'date' => date('d/m/Y H:i', strtotime($this->approved_at))];
        } else {
            $steps[] = ['name' => 'รออนุมัติ', 'status' => 0, 'date' => ''];
        }

        // 4. จ่ายเงิน/PV
        $refs = \backend\models\PaymentVoucherRef::find()
            ->alias('r')
            ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
            ->where(['r.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PRE_ADVANCE, 'r.ref_id' => $this->id])
            ->andWhere(['!=', 'pv.status', \backend\models\PaymentVoucher::STATUS_CANCELLED])
            ->all();

        $has_pv = false;
        $has_paid = false;
        foreach ($refs as $ref) {
            $has_pv = true;
            if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_COMPLETED || $ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_ACTIVE) { 
                $has_paid = true;
            }
        }

        if ($has_paid) {
            $steps[] = ['name' => 'จ่ายเงินแล้ว', 'status' => 1, 'date' => ''];
        } else if ($has_pv) {
            $steps[] = ['name' => 'สร้าง PV (รอจ่าย)', 'status' => 1, 'date' => ''];
        } else {
            $steps[] = ['name' => 'สร้าง PV / จ่ายเงิน', 'status' => 0, 'date' => ''];
        }

        $html = '<ul class="list-unstyled mb-0 text-start px-2 py-1">';
        $latest = 'สร้างใบเบิก';
        foreach ($steps as $s) {
            $icon = $s['status'] == 1 ? '<i class="fas fa-check-circle text-success me-2"></i>' : ($s['status'] == 2 ? '<i class="fas fa-times-circle text-danger me-2"></i>' : '<i class="far fa-circle text-muted me-2"></i>');
            $textClass = $s['status'] == 1 ? 'text-success fw-bold' : ($s['status'] == 2 ? 'text-danger fw-bold' : 'text-muted');
            $html .= '<li class="mb-2 ' . $textClass . '">' . $icon . $s['name'] . ($s['date'] ? ' <small class="text-secondary ms-1">('.$s['date'].')</small>' : '') . '</li>';
            if ($s['status'] == 1) $latest = $s['name'];
        }
        $html .= '</ul>';

        return [
            'latest' => $latest,
            'html' => $html
        ];
    }
}
