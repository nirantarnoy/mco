<?php

namespace backend\models;

use backend\models\JournalTransLine;
use Yii;
use yii\db\ActiveRecord;


date_default_timezone_set('Asia/Bangkok');

class Product extends \common\models\Product
{
    public function behaviors()
    {
        return [
            'timestampcdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                ],
                'value' => time(),
            ],
            'timestampudate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'updated_at',
                ],
                'value' => time(),
            ],
            'timestampcby' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_by',
                ],
                'value' => Yii::$app->user->id,
            ],
            'timestampcby' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
                'value' => Yii::$app->user->id,
            ],
//            'timestampcompany' => [
//                'class' => \yii\behaviors\AttributeBehavior::className(),
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_INSERT => 'company_id',
//                ],
//                'value' => isset($_SESSION['user_company_id']) ? $_SESSION['user_company_id'] : 1,
//            ],
//            'timestampbranch' => [
//                'class' => \yii\behaviors\AttributeBehavior::className(),
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_INSERT => 'branch_id',
//                ],
//                'value' => isset($_SESSION['user_branch_id']) ? $_SESSION['user_branch_id'] : 1,
//            ],
            'timestampupdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => time(),
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockSums()
    {
        return $this->hasMany(\backend\models\StockSum::class, ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTransLines()
    {
        return $this->hasMany(JournalTransLine::class, ['product_id' => 'id']);
    }

    /**
     * Get stock in specific warehouse
     */
    public function getStockInWarehouse($warehouseId)
    {
        $stockSum = \backend\models\StockSum::find()
            ->where(['product_id' => $this->id, 'warehouse_id' => $warehouseId])
            ->one();

        return $stockSum ? $stockSum->qty : 0;
    }

    /**
     * Get available stock in specific warehouse
     */
    public function getAvailableStockInWarehouse($warehouseId)
    {
        $stockSum = \backend\models\StockSum::find()
            ->where(['product_id' => $this->id, 'warehouse_id' => $warehouseId])
            ->one();

        return $stockSum ? $stockSum->getAvailableQty() : 0;
    }

    /**
     * Check if product is low stock
     */
    public function isLowStock()
    {
        return $this->stock_qty <= $this->minimum_stock;
    }

}
