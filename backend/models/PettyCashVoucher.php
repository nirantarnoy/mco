<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "petty_cash_voucher".
 *
 * @property int $id
 * @property string $pcv_no
 * @property string $date
 * @property string $name
 * @property float $amount
 * @property string|null $paid_for
 * @property string|null $issued_by
 * @property string|null $issued_date
 * @property string|null $approved_by
 * @property string|null $approved_date
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PettyCashDetail[] $details
 */
class PettyCashVoucher extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'petty_cash_voucher';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'amount'], 'required'],
            [['pcv_no','date', 'issued_date', 'approved_date','created_by','updated_by', 'recall_at'], 'safe'],
            [['amount'], 'number', 'min' => 0],
            [['paid_for', 'recall_reason'], 'string'],
            [['status','quotation_id','customer_id','pay_for_emp_id','job_id','vendor_id','approve_status', 'is_recall', 'recall_by'], 'integer'],
            [['pcv_no'], 'string', 'max' => 50],
            [['name', 'issued_by', 'approved_by'], 'string', 'max' => 255],
            [['pcv_no'], 'unique'],
            [['name'],'default','value' => ''],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pcv_no' => 'PCV No.',
            'date' => 'วันที่',
            'name' => 'ชื่อผู้รับเงิน',
            'amount' => 'จำนวนเงิน',
            'paid_for' => 'จ่ายเพื่อ',
            'issued_by' => 'ผู้จัดทำ',
            'issued_date' => 'วันที่จัดทำ',
            'approved_by' => 'ผู้อนุมัติ',
            'approved_date' => 'วันที่อนุมัติ',
            'status' => 'สถานะ',
            'approve_status' => 'สถานะการอนุมัติ',
            'is_recall' => 'สถานะขอ Recall',
            'recall_reason' => 'เหตุผลการขอ Recall',
            'recall_by' => 'ผู้ขอ Recall',
            'recall_at' => 'เวลาที่ขอ Recall',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขล่าสุด',
            'quotation_id' => 'ใบเสนอราคา',
            'pay_for_emp_id' => 'พนักงาน',
            'customer_id' => 'ลูกค้า',
            'job_id' => 'ใบงาน',
            'vendor_id' => 'ผู้จำหน่าย',
            'approve_status' => 'สถานะการอนุมัติ'
        ];
    }

    /**
     * Gets query for [[Details]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetails()
    {
        return $this->hasMany(PettyCashDetail::class, ['voucher_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * Generate PCV Number
     */
    public function generatePcvNo()
    {
        $target_date = $this->date;
        // Handle d/m/Y format if not yet converted
        if (strpos($target_date, '/') !== false) {
            $dateParts = explode('/', $target_date);
            if (count($dateParts) == 3) {
                $target_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
        }

        $date = $target_date ? strtotime($target_date) : time();
        $prefix = 'PCV' . date('y', $date) . str_pad(date('m', $date), 2, '0', STR_PAD_LEFT);
        
        $lastRecord = self::find()
            ->where(['like', 'pcv_no', $prefix . '%', false])
            ->orderBy(['pcv_no' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->pcv_no, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%03d', $newNumber);
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Convert date from d/m/Y to Y-m-d first so generatePcvNo can use it
            if (!empty($this->date) && strpos($this->date, '/') !== false) {
                $dateParts = explode('/', $this->date);
                if (count($dateParts) == 3) {
                    $this->date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }

            if ($insert && empty($this->pcv_no)) {
                $this->pcv_no = $this->generatePcvNo();
            }
            $this->company_id = (\Yii::$app->session->get('company_id') == 100 ? null : \Yii::$app->session->get('company_id'));

            return true;
        }
        $this->company_id = 1;

        return false;
    }

    /**
     * Calculate total amount from details
     */
    public function calculateTotalAmount()
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->total;
        }
        return $total;
    }

    /**
     * Update amount from details
     */
    public function updateAmountFromDetails()
    {
        unset($this->details);
        $this->amount = $this->calculateTotalAmount();
        return $this->save(false);
    }

    /**
     * Calculate total VAT
     */
    public function getTotalVat()
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->vat;
        }
        return $total;
    }

    /**
     * Calculate total VAT Prohibit
     */
    public function getTotalVatProhibit()
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->vat_prohibit;
        }
        return $total;
    }

    /**
     * Calculate total WHT
     */
    public function getTotalWht()
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->wht;
        }
        return $total;
    }

    /**
     * Calculate total Other
     */
    public function getTotalOther()
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->other;
        }
        return $total;
    }

    public function getRecallUser()
    {
        return $this->hasOne(\backend\models\User::class, ['id' => 'recall_by']);
    }

    public function getDocSlips()
    {
        return $this->hasMany(\common\models\PettyCashVoucherDocSlip::class, ['petty_cash_voucher_id' => 'id']);
    }

    public function getDocBills()
    {
        return $this->hasMany(\common\models\PettyCashVoucherDocBill::class, ['petty_cash_voucher_id' => 'id']);
    }

    /**
     * Check attached documents for slip and bill
     * @return array
     */
    public function getAttachedDocuments()
    {
        $hasSlip = !empty($this->docSlips);
        $hasBill = !empty($this->docBills);

        return [
            'has_slip' => $hasSlip,
            'has_bill' => $hasBill,
            'is_complete' => ($hasSlip && $hasBill),
        ];
    }

    /**
     * Render HTML badge for attachment status
     * @return string
     */
    public function getDocStatusHtml()
    {
        $status = $this->getAttachedDocuments();
        $html = '';

        if ($status['is_complete']) {
            $html .= '<span class="badge badge-success bg-success me-1 mb-1" style="font-size: 11px;"><i class="fas fa-check-circle"></i> แนบเอกสารครบ</span>';
        } else {
            $html .= '<span class="badge badge-warning bg-warning text-dark me-1 mb-1" style="font-size: 11px;"><i class="fas fa-exclamation-circle"></i> ยังไม่ครบ</span>';
        }

        $html .= '<div class="mt-1">';
        if ($status['has_slip']) {
            $html .= '<span class="badge badge-info bg-info text-white me-1" title="แนบสลิปแล้ว"><i class="fas fa-check"></i> สลิป</span>';
        } else {
            $html .= '<span class="badge badge-light bg-light text-muted border me-1" title="ยังไม่แนบสลิป"><i class="fas fa-times text-danger"></i> สลิป</span>';
        }

        if ($status['has_bill']) {
            $html .= '<span class="badge badge-primary bg-primary text-white" title="แนบใบเสร็จแล้ว"><i class="fas fa-check"></i> ใบเสร็จ</span>';
        } else {
            $html .= '<span class="badge badge-light bg-light text-muted border" title="ยังไม่แนบใบเสร็จ"><i class="fas fa-times text-danger"></i> ใบเสร็จ</span>';
        }
        $html .= '</div>';

        return $html;
    }

    public static function ensureRecallColumns()
    {
        try {
            $db = Yii::$app->db;
            $table = $db->getTableSchema(self::tableName(), true);
            if ($table) {
                if (!isset($table->columns['is_recall'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'is_recall', 'INT(11) DEFAULT 0')->execute();
                }
                if (!isset($table->columns['recall_reason'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'recall_reason', 'TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL')->execute();
                } else {
                    $db->createCommand("ALTER TABLE " . self::tableName() . " MODIFY `recall_reason` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL")->execute();
                }
                if (!isset($table->columns['recall_by'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'recall_by', 'INT(11) NULL')->execute();
                }
                if (!isset($table->columns['recall_at'])) {
                    $db->createCommand()->addColumn(self::tableName(), 'recall_at', 'DATETIME NULL')->execute();
                }
                $db->getSchema()->refreshTableSchema(self::tableName());
            }
        } catch (\Exception $e) {
            Yii::error('Failed to ensure recall columns for petty_cash_voucher: ' . $e->getMessage());
        }
    }
}