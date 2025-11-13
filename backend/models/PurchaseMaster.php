<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "purchase_master".
 *
 * @property int $id
 * @property string $docnum
 * @property string $docdat
 * @property string|null $supcod
 * @property string|null $supnam
 * @property string|null $job_no
 * @property string|null $paytrm
 * @property string|null $duedat
 * @property string|null $taxid
 * @property string|null $discod
 * @property string|null $addr01
 * @property string|null $addr02
 * @property string|null $addr03
 * @property string|null $zipcod
 * @property string|null $telnum
 * @property string|null $orgnum
 * @property string|null $refnum
 * @property string|null $vatdat
 * @property float|null $vatpr0
 * @property float|null $amount
 * @property float|null $unitpr
 * @property string|null $disc
 * @property float|null $vat_percent
 * @property float|null $vat_amount
 * @property float|null $tax_percent
 * @property float|null $tax_amount
 * @property float|null $total_amount
 * @property string|null $remark
 * @property int|null $status
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property PurchaseDetail[] $purchaseDetails
 */
class PurchaseMaster extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purchase_master';
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
            [['docnum', 'docdat'], 'required'],
            [['docdat', 'duedat', 'vatdat'], 'safe'],
            [['vatpr0', 'amount', 'unitpr', 'vat_percent', 'vat_amount', 'tax_percent', 'tax_amount', 'total_amount'], 'number'],
            [['remark'], 'string'],
            [['status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['docnum', 'job_no', 'discod', 'orgnum', 'refnum', 'disc'], 'string', 'max' => 50],
            [['supcod', 'taxid'], 'string', 'max' => 20],
            [['supnam', 'addr01', 'addr02', 'addr03'], 'string', 'max' => 255],
            [['paytrm'], 'string', 'max' => 100],
            [['zipcod'], 'string', 'max' => 10],
            [['telnum'], 'string', 'max' => 50],
            [['docnum'], 'unique'],
            [['vat_percent'], 'default', 'value' => 7],
            [['status'], 'default', 'value' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'docnum' => 'เลขที่เอกสาร',
            'docdat' => 'วันที่เอกสาร',
            'supcod' => 'รหัสผู้จำหน่าย',
            'supnam' => 'ชื่อผู้จำหน่าย',
            'job_no' => 'JOB No.',
            'paytrm' => 'เครดิต',
            'duedat' => 'วันครบกำหนด',
            'taxid' => 'เลขประจำตัวผู้เสียภาษี',
            'discod' => 'ส่วนลด',
            'addr01' => 'ที่อยู่ 1',
            'addr02' => 'ที่อยู่ 2',
            'addr03' => 'ที่อยู่ 3',
            'zipcod' => 'รหัสไปรษณีย์',
            'telnum' => 'เบอร์โทร',
            'orgnum' => 'สาขาเรา',
            'refnum' => 'เลขที่ใบกำกับ',
            'vatdat' => 'วันที่ภาษี',
            'vatpr0' => 'มูลค่าก่อนภาษี',
            'amount' => 'จำนวนเงิน',
            'unitpr' => 'ราคาต่อหน่วย',
            'disc' => 'ส่วนลด',
            'vat_percent' => 'VAT %',
            'vat_amount' => 'จำนวน VAT',
            'tax_percent' => 'TAX %',
            'tax_amount' => 'จำนวน TAX',
            'total_amount' => 'รวมสุทธิ',
            'remark' => 'หมายเหตุ',
            'status' => 'สถานะ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
            'created_by' => 'สร้างโดย',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * Gets query for [[PurchaseDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, ['purchase_master_id' => 'id'])->orderBy(['line_no' => SORT_ASC]);
    }

    /**
     * คำนวณยอดรวม
     */
    public function calculateTotals()
    {
        $this->vatpr0 = 0;

        foreach ($this->purchaseDetails as $detail) {
            $this->vatpr0 += $detail->amount;
        }

        // คำนวณ VAT
        $this->vat_amount = ($this->vatpr0 * $this->vat_percent) / 100;

        // คำนวณ TAX
        $this->tax_amount = ($this->vatpr0 * $this->tax_percent) / 100;

        // คำนวณรวมสุทธิ
        $this->total_amount = $this->vatpr0 + $this->vat_amount - $this->tax_amount;
    }

    /**
     * สร้างเลขที่เอกสารอัตโนมัติ
     */
    public static function generateDocnum()
    {
        $prefix = 'PO';
        $date = date('Ymd');

        $lastDoc = self::find()
            ->where(['like', 'docnum', $prefix . $date])
            ->orderBy(['docnum' => SORT_DESC])
            ->one();

        if ($lastDoc) {
            $lastNumber = (int)substr($lastDoc->docnum, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}