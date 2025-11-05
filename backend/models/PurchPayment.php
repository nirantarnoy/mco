<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "purch-payment".
 *
 * @property int $id
 * @property int $purch_id
 * @property string|null $trans_date
 * @property string|null $status
 * @property int|null $created_by
 * @property string|null $created_at
 *
 * @property Purch $purch
 * @property PurchPaymentLine[] $purchPaymentLines
 */
class PurchPayment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_payment';
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
                'updatedAtAttribute' => false,
                'value' => date('Y-m-d H:i:s'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_id'], 'required'],
            [['purch_id', 'created_by'], 'integer'],
            [['trans_date', 'created_at'], 'safe'],
            [['status'], 'string', 'max' => 50],
            [['purch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Purch::class, 'targetAttribute' => ['purch_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_id' => 'ใบสั่งซื้อ',
            'trans_date' => 'วันที่โอนเงิน',
            'status' => 'สถานะ',
            'created_by' => 'สร้างโดย',
            'created_at' => 'สร้างเมื่อ',
        ];
    }

    /**
     * Gets query for [[Purch]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurch()
    {
        return $this->hasOne(Purch::class, ['id' => 'purch_id']);
    }

    /**
     * Gets query for [[PurchPaymentLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchPaymentLines()
    {
        return $this->hasMany(PurchPaymentLine::class, ['purch_payment_id' => 'id']);
    }
}