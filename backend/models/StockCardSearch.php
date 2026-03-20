<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use backend\models\StockTrans;
use backend\models\Product;

/**
 * StockCardSearch represents the search logic for the Stock Card report.
 */
class StockCardSearch extends Model
{
    public $product_id;
    public $from_date;
    public $to_date;
    public $warehouse_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'warehouse_id'], 'integer'],
            [['from_date', 'to_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Get stock movement data for the report
     */
    public function getData()
    {
        $company_id = Yii::$app->session->get('company_id');

        // Initial balance query (quantity before from_date)
        $initialBalance = 0;
        if ($this->from_date && $this->product_id) {
            $inbound = StockTrans::find()
                ->where(['stock_trans.product_id' => $this->product_id])
                ->andWhere(['stock_trans.stock_type_id' => 1]) // Stock In
                ->andWhere(['<', 'stock_trans.trans_date', $this->from_date])
                ->andWhere(['stock_trans.company_id' => $company_id])
                ->sum('qty') ?: 0;

            $outbound = StockTrans::find()
                ->where(['stock_trans.product_id' => $this->product_id])
                ->andWhere(['stock_trans.stock_type_id' => 2]) // Stock Out
                ->andWhere(['<', 'stock_trans.trans_date', $this->from_date])
                ->andWhere(['stock_trans.company_id' => $company_id])
                ->sum('qty') ?: 0;

            $initialBalance = $inbound - $outbound;
        }

        // Transactions within date range
        $query = StockTrans::find()
            ->joinWith(['journalTrans', 'product'])
            ->where(['stock_trans.company_id' => $company_id]);

        if ($this->product_id) {
            $query->andWhere(['stock_trans.product_id' => $this->product_id]);
        }

        if ($this->warehouse_id) {
            $query->andWhere(['stock_trans.warehouse_id' => $this->warehouse_id]);
        }

        if ($this->from_date) {
            $query->andWhere(['>=', 'stock_trans.trans_date', $this->from_date]);
        }

        if ($this->to_date) {
            $query->andWhere(['<=', 'stock_trans.trans_date', $this->to_date]);
        }

        $transData = $query->orderBy(['stock_trans.trans_date' => SORT_ASC, 'stock_trans.id' => SORT_ASC])->all();

        return [
            'initialBalance' => $initialBalance,
            'transactions' => $transData,
        ];
    }

    /**
     * Get product list for dropdown
     */
    public static function getProductList()
    {
        return \yii\helpers\ArrayHelper::map(
            Product::find()->orderBy('name')->all(),
            'id',
            function($model) {
                return $model->code . ' - ' . $model->name;
            }
        );
    }

    /**
     * Get warehouse list for dropdown
     */
    public static function getWarehouseList()
    {
        return \yii\helpers\ArrayHelper::map(
            Warehouse::find()->all(),
            'id',
            'name'
        );
    }
}
