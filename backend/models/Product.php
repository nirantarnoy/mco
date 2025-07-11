<?php

namespace backend\models;

use common\models\JournalTransLine;
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

    public function getJournaltransLine()
    {
        return $this->hasMany(JournalTransLine::class, ['product_id' => 'id']);
    }

    public function getJournalTrans()
    {
        return $this->hasMany(JournalTrans::class, ['id' => 'journal_trans_id'])
            ->via('journaltransLine');
    }

//    public function getWatchMaker()
//    {
//        return $this->hasMany(Watchmaker::class, ['id' => 'party_id'])
//            ->via('journalTrans');
//    }


    public static function findCode($id){
        $model = Product::find()->where(['id'=>$id])->one();
        return $model != null ?$model->name:'';
    }
    public static function findSku($id){
        $model = Product::find()->where(['id'=>$id])->one();
        return $model != null ?$model->name:'';
    }
    public static function findBarCode($id){
        $model = Product::find()->where(['id'=>$id])->one();
        return $model != null ?$model->barcode:'';
    }
    public static function findName($id){
        $model = Product::find()->where(['id'=>$id])->one();
        return $model != null ?$model->name:'';
    }
    public static function findPrice($id){
        $model = Product::find()->where(['id'=>$id])->one();
        return $model != null ?$model->sale_price:0;
    }
    public static function findDesc($id){
        $model = Product::find()->where(['id'=>$id])->one();
        return $model != null ?$model->description:'';
    }
    public static function findPhoto($id){
        $model = Product::find()->where(['id'=>$id])->one();
        return $model != null ?$model->photo:'';
    }

    public static function findUnitId($product_id){
        $model = Product::find()->where(['id'=>$product_id])->one();
        return $model != null ?$model->unit_id:0;
    }

    public static function getTotalQty($id){
        $model = \backend\models\Stocksum::find()->where(['product_id'=>$id])->sum('qty');
        return $model;
    }

    public static function getWarehouseName($product_id,$qty){
        $name = '';
        if($product_id && $qty){
            $model = \backend\models\Stocksum::find()->where(['product_id'=>$product_id])->andFilterWhere(['>=','qty',$qty])->one();
            if($model){
                $model_warehouse = \backend\models\Warehouse::find()->where(['id'=>$model->warehouse_id])->one();
                if($model_warehouse){
                    $name = $model_warehouse->name;
                }
            }
        }
        return $name;
    }

    /**
     * Get products for dropdown
     */
    public static function getProductList()
    {
        return self::find()
            ->where(['status' => 1])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Get product info for AJAX
     */
    public static function getProductInfo($id)
    {
        $product = self::findOne($id);
        if ($product) {
            return [
                'id' => $product->id,
                'code' => $product->name,
                'name' => $product->name,
                'sale_price' => $product->salet_price,
                'unit_id' => $product->unit_id,
            ];
        }
        return null;
    }



}
