<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "journal_trans_line".
 *
 * @property int $id
 * @property int|null $journal_trans_id
 * @property int|null $product_id
 * @property int|null $warehouse_id
 * @property float|null $qty
 * @property string|null $remark
 *
 * @property JournalTrans $journalTrans
 * @property Product $product
 * @property Warehouse $warehouse
 */
class JournalTransLine extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'journal_trans_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['journal_trans_id', 'product_id', 'warehouse_id'], 'integer'],
            [['qty'], 'number'],
            [['qty'], 'required'],
            [['qty'], 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['remark'], 'string', 'max' => 255],
            [['journal_trans_id'], 'exist', 'skipOnError' => true, 'targetClass' => JournalTrans::class, 'targetAttribute' => ['journal_trans_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'journal_trans_id' => 'รหัส Journal Transaction',
            'product_id' => 'รหัสสินค้า',
            'warehouse_id' => 'รหัสคลังสินค้า',
            'qty' => 'จำนวน',
            'remark' => 'หมายเหตุ',
        ];
    }

    /**
     * Gets query for [[JournalTrans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTrans()
    {
        return $this->hasOne(JournalTrans::class, ['id' => 'journal_trans_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[Warehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }
}