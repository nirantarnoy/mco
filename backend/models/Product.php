<?php

namespace backend\models;

use backend\models\JournalTransLine;
use Yii;
use yii\db\ActiveRecord;


date_default_timezone_set('Asia/Bangkok');

class Product extends \common\models\Product
{
    public $is_adjust_stock;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['is_adjust_stock', 'company_list'], 'safe'],
        ]);
    }

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
                'value' => function () {
                    return (Yii::$app->user && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null;
                },
            ],
            'timestampuby' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
                'value' => function () {
                    return (Yii::$app->user && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null;
                },
            ],
            'timestampcompany' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'company_id',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'company_id',
                ],
                'value' => function ($event) {
                    if ($this->is_common == 1) {
                        return null;
                    }
                    if (is_array($this->company_list) && !empty($this->company_list)) {
                        return (int)reset($this->company_list);
                    }
                    if (isset(Yii::$app->session) && Yii::$app->session->has('company_id')) {
                        $cId = Yii::$app->session->get('company_id');
                        return $cId == 100 ? null : (int)$cId;
                    }
                    return null;
                },
            ],
         
            'timestampupdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => time(),
            ],
        ];
    }

    public static function findName($id)
    {
        $model = Product::find()
            ->where(['id' => $id])
            ->one();

        return $model != null ? $model->code.' - '.$model->name : '';
    }

    public static function findUnitName($id)
    {
        $unit_name = '';
        $model = Product::find()
            ->where(['id' => $id])
            ->one();
            if($model){
                if($model->unit_id != null){
                    $unit_name = $model->unit->name;
                }
            }
        return $unit_name;
    }

    public static function findWarehouseOnhand($id)
    {
        $name = '';
        $model = \backend\models\StockSum::find()
            ->where(['product_id' => $id])->all();
        foreach ($model as $value) {
            if($value->qty <= 0)continue;
            $name .= '<div class="badge badge-pill badge-info" style="padding: 10px;margin: 3px">'.\backend\models\Warehouse::findName($value->warehouse_id) . '</div><br />';
        }
        return $name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockSums()
    {
        return $this->hasMany(\backend\models\StockSum::class, ['product_id' => 'id']);
    }

    public function getWarehouse()
    {
        return $this->hasOne(\backend\models\Warehouse::class, ['id' => 'warehouse_id']);
    }

    public function getUnit(){
        return $this->hasOne(Unit::class, ['id' => 'unit_id']);
    }

    public function getProductGroup()
    {
        return $this->hasOne(Productgroup::class, ['id' => 'product_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJournaltransLine()
    {
        return $this->hasMany(JournalTransLine::class, ['product_id' => 'id']);
    }

    /**
     * Get stock in specific warehouse
     */
    public function getStockInWarehouse($warehouseId)
    {
        $qty = \backend\models\StockSum::find()
            ->where(['product_id' => $this->id, 'warehouse_id' => $warehouseId])
            ->sum('qty');

        return $qty ?: 0;
    }

    /**
     * Get available stock in specific warehouse
     */
    public function getAvailableStockInWarehouse($warehouseId)
    {
        $qty = \backend\models\StockSum::find()
            ->where(['product_id' => $this->id, 'warehouse_id' => $warehouseId])
            ->sum('qty');
            
        $reservQty = \backend\models\StockSum::find()
            ->where(['product_id' => $this->id, 'warehouse_id' => $warehouseId])
            ->sum('reserv_qty');

        return ($qty ?: 0) - ($reservQty ?: 0);
    }

    /**
     * Check if product is low stock
     */
    public function isLowStock()
    {
        return $this->stock_qty <= $this->minimum_stock;
    }
    public static function findProductName($id){
        $model = Product::find()->where(['id' => $id])->one();
        if($model){
            return $model->code.' - '.$model->name;
        }else{
            return '';
        }
    }

    public $company_list = [];

    public function getProductCompanies()
    {
        return $this->hasMany(\common\models\ProductCompany::class, ['product_id' => 'id']);
    }

    public function getCompanies()
    {
        return $this->hasMany(\backend\models\Company::class, ['id' => 'company_id'])->via('productCompanies');
    }

    public function afterFind()
    {
        parent::afterFind();
        if ($this->is_common != 1) {
            $this->company_list = \common\models\ProductCompany::find()
                ->select('company_id')
                ->where(['product_id' => $this->id])
                ->column();
            if (empty($this->company_list) && !empty($this->company_id)) {
                $this->company_list = [(int)$this->company_id];
            }
        }
    }

    /**
     * ตรวจสอบว่าสินค้ารายการนี้อนุญาตให้ใช้งานในบริษัทที่ระบุหรือไม่
     * @param int|null $company_id
     * @return bool
     */
    public function isAllowedForCompany($company_id = null)
    {
        if ($this->is_common == 1) {
            return true;
        }
        if ($company_id === null) {
            $company_id = (Yii::$app->has('session') && Yii::$app->session) ? Yii::$app->session->get('company_id') : null;
        }
        if (!$company_id || $company_id == 100) {
            return true;
        }
        $allowed = \common\models\ProductCompany::find()
            ->where(['product_id' => $this->id, 'company_id' => $company_id])
            ->exists();
        if ($allowed) {
            return true;
        }
        return (int)$this->company_id === (int)$company_id;
    }

    public static function getAllowedProducts($company_id = null)
    {
        if ($company_id === null) {
            $sess = (Yii::$app->has('session') && Yii::$app->session) ? Yii::$app->session->get('company_id') : null;
            $company_id = ($sess == 100 ? null : $sess);
        }
        $query = Product::find();
        if ($company_id != null) {
            $subQuery = (new \yii\db\Query())
                ->select('product_id')
                ->from('product_company')
                ->where(['company_id' => $company_id]);

            $query->andWhere([
                'or',
                ['product.is_common' => 1],
                ['product.company_id' => $company_id],
                ['product.id' => $subQuery]
            ]);
        }
        return $query;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        \common\models\ProductCompany::deleteAll(['product_id' => $this->id]);
        if ($this->is_common != 1 && is_array($this->company_list)) {
            foreach ($this->company_list as $cid) {
                $cid = (int)$cid;
                if ($cid > 0) {
                    $map = new \common\models\ProductCompany();
                    $map->product_id = $this->id;
                    $map->company_id = $cid;
                    $map->created_at = time();
                    $map->save(false);
                }
            }
        }
    }

    public static function findDescription($id){
        $name = '';
        $model = \backend\models\Product::find()->where(['id' => $id])->one();
        if($model){
            $name = $model->name;
        }
        return $name;
    }

    public static function findCode($id){
        $name = '';
        $model = \backend\models\Product::find()->where(['id' => $id])->one();
        if($model){
            $name = $model->code;
        }
        return $name;
    }
    public static function findModelName($id){
        $name = '';
        $model = \backend\models\Product::find()->where(['id' => $id])->one();
        if($model){
            $name = $model->model_name;
        }
        return $name;
    }

    public static function findBrand($id){
        $name = '';
        $model = \backend\models\Product::find()->where(['id' => $id])->one();
        if($model){
            $model_brand = \backend\models\Productbrand::find()->where(['id' => $model->brand_id])->one();
            if($model_brand){
                $name = $model_brand->name;
            }
        }
        return $name;
    }

}
