<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_trans".
 *
 * @property int $id
 * @property int $journal_trans_id
 * @property string $trans_date
 * @property int $product_id
 * @property int $trans_type_id
 * @property float $qty
 * @property string $created_at
 * @property string $created_by
 * @property string $status
 * @property string $remark
 * @property int $stock_type_id
 * @property int $warehouse_id
 * @property float $line_price
 * @property string $updated_at
 *
 * @property JournalTrans $journalTrans
 * @property Product $product
 */
class StockTrans extends ActiveRecord
{

    const STATUS_CANCELLED = 500;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_trans';
    }

    public function behaviors()
    {
        return [
            'timestampcdate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_at',
                ],
                'value'=> time(),
            ],
            'timestampudate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'updated_at',
                ],
                'value'=> time(),
            ],
            'timestampcby'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_by',
                ],
                'value'=> Yii::$app->user->id,
            ],
            'timestamuby'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_UPDATE=>'updated_by',
                ],
                'value'=> Yii::$app->user->id,
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['journal_trans_id', 'trans_date', 'product_id', 'trans_type_id', 'qty'], 'required'],
            [['journal_trans_id', 'product_id', 'trans_type_id', 'stock_type_id', 'warehouse_id', 'status'], 'integer'],
            [['trans_date', 'created_at', 'updated_at'], 'safe'],
            [['qty', 'line_price'], 'number'],
            [['remark'], 'string'],
            [['created_by'], 'string', 'max' => 255],
            [['journal_trans_id'], 'exist', 'skipOnError' => true, 'targetClass' => JournalTrans::class, 'targetAttribute' => ['journal_trans_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'journal_trans_id' => 'รายการอ้างอิง',
            'trans_date' => 'วันที่ทำรายการ',
            'product_id' => 'สินค้า',
            'trans_type_id' => 'ประเภทรายการ',
            'qty' => 'จำนวน',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'status' => 'สถานะ',
            'remark' => 'หมายเหตุ',
            'stock_type_id' => 'ประเภทสต็อก',
            'warehouse_id' => 'คลัง',
            'line_price' => 'Line Price',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTrans()
    {
        return $this->hasOne(JournalTrans::class, ['id' => 'journal_trans_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function beforeSave($insert){
        $this->company_id = \Yii::$app->session->get('company_id');
        return true;
    }
}