<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "purchase_detail".
 *
 * @property int $id
 * @property int $purchase_master_id
 * @property int $line_no
 * @property string|null $stkcod
 * @property string|null $stkdes
 * @property float|null $uqnty
 * @property float|null $unitpr
 * @property float|null $amount
 * @property string|null $disc
 * @property string|null $remark
 * @property int $created_at
 * @property int $updated_at
 *
 * @property PurchaseMaster $purchaseMaster
 */
class PurchaseDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purchase_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purchase_master_id', 'line_no'], 'required'],
            [['purchase_master_id', 'line_no', 'created_at', 'updated_at'], 'integer'],
            [['uqnty', 'unitpr', 'amount'], 'number'],
            [['remark'], 'string'],
            [['stkcod'], 'string', 'max' => 50],
            [['stkdes'], 'string', 'max' => 255],
            [['disc'], 'string', 'max' => 50],
            [['purchase_master_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseMaster::class, 'targetAttribute' => ['purchase_master_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purchase_master_id' => 'รหัสใบซื้อ',
            'line_no' => 'ลำดับที่',
            'stkcod' => 'รหัสสินค้า',
            'stkdes' => 'รายละเอียดสินค้า',
            'uqnty' => 'จำนวน',
            'unitpr' => 'ราคา/หน่วย',
            'amount' => 'จำนวนเงิน',
            'disc' => 'ส่วนลด',
            'remark' => 'หมายเหตุ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    /**
     * Gets query for [[PurchaseMaster]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseMaster()
    {
        return $this->hasOne(PurchaseMaster::class, ['id' => 'purchase_master_id']);
    }

    /**
     * คำนวณยอดเงิน
     */
    public function calculateAmount()
    {
        $this->amount = $this->uqnty * $this->unitpr;

        // ถ้ามีส่วนลด (รูปแบบ 10% หรือ 100)
        if ($this->disc) {
            if (strpos($this->disc, '%') !== false) {
                $discPercent = floatval(str_replace('%', '', $this->disc));
                $this->amount = $this->amount - ($this->amount * $discPercent / 100);
            } else {
                $this->amount = $this->amount - floatval($this->disc);
            }
        }

        return $this->amount;
    }
}