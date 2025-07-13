<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "warehouse".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 *
 * @property StockSum[] $stockSums
 * @property StockTrans[] $stockTrans
 * @property JournalTransLine[] $journalTransLines
 */
class Warehouse extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warehouse';
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
            [['status', 'created_at', 'created_by'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['name'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อคลังสินค้า',
            'description' => 'รายละเอียด',
            'status' => 'สถานะ',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
        ];
    }

    public static function findName($id) {
        $model = Warehouse::find()->where(['id'=>$id])->one();
        return $model!= null?$model->name:'';
    }

    /**
     * Gets query for [[StockSums]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockSums()
    {
        return $this->hasMany(StockSum::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[StockTrans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockTrans()
    {
        return $this->hasMany(StockTrans::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[JournalTransLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTransLines()
    {
        return $this->hasMany(JournalTransLine::class, ['warehouse_id' => 'id']);
    }

    /**
     * Get warehouse list for dropdown
     */
    public static function getWarehouseList()
    {
        return self::find()
            ->where(['status' => self::STATUS_ACTIVE])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return $this->status == self::STATUS_ACTIVE ? 'ใช้งาน' : 'ไม่ใช้งาน';
    }
}