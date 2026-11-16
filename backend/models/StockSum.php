<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_sum".
 *
 * @property int $id
 * @property int $product_id
 * @property int $warehouse_id
 * @property float $qty
 * @property string $updated_at
 * @property float $reserve_qty
 * @property string $created_at
 *
 * @property Product $product
 */
class StockSum extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_sum';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'warehouse_id'], 'required'],
            [['product_id', 'warehouse_id'], 'integer'],
            [['qty', 'reserv_qty'], 'number'],
            [['updated_at', 'created_at'], 'safe'],
            [['lot_no'], 'string', 'max' => 50],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['product_id', 'warehouse_id', 'lot_no'], 'unique', 'targetAttribute' => ['product_id', 'warehouse_id', 'lot_no']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'warehouse_id' => 'Warehouse ID',
            'qty' => 'Qty',
            'updated_at' => 'Updated At',
            'reserv_qty' => 'Reserve Qty',
            'created_at' => 'Created At',
            'lot_no' => 'Lot No',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Get available quantity (total qty - reserved qty)
     */
    public function getAvailableQty()
    {
        return $this->qty - $this->reserv_qty;
    }

    public static function updateStock($productId, $warehouseId, $qty, $direction, $lotNo = null)
    {
        $query = self::find()->where(['product_id' => $productId, 'warehouse_id' => $warehouseId]);
        if ($lotNo) {
            $query->andWhere(['lot_no' => $lotNo]);
        } else {
            $query->andWhere(['OR', ['lot_no' => null], ['lot_no' => '']]);
        }
        $stockSum = $query->one();

        if (!$stockSum) {
            $stockSum = new self();
            $stockSum->product_id = $productId;
            $stockSum->warehouse_id = $warehouseId;
            $stockSum->lot_no = $lotNo;
            $stockSum->qty = 0;
            $stockSum->reserv_qty = 0;
            $stockSum->created_at = date('Y-m-d H:i:s');
        }

        if ($direction > 0) {
            $stockSum->qty += $qty;
        } else {
            $stockSum->qty -= $qty;
        }

        $stockSum->updated_at = date('Y-m-d H:i:s');
        if ($stockSum->save(false)) {
            self::updateProductStock($productId);
        }
        return $stockSum;
    }

    /**
     * Deduct stock using FIFO logic based on lot_no (oldest first).
     * Returns an array of deductions: [['lot_no' => 'xxx', 'qty' => 10], ...]
     */
    public static function deductStockFIFO($productId, $warehouseId, $qtyToDeduct)
    {
        $deductions = [];
        $remainingToDeduct = $qtyToDeduct;

        // Get all available lots, ordered by created_at ASC (oldest first)
        // Ensure true FIFO behavior based on the time the stock was received.
        $lots = self::find()
            ->where(['product_id' => $productId, 'warehouse_id' => $warehouseId])
            ->andWhere(['>', 'qty', 0])
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        foreach ($lots as $lot) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $available = $lot->qty;
            if ($available >= $remainingToDeduct) {
                // This lot can fulfill the remaining deduction
                $deductedQty = $remainingToDeduct;
                $lot->qty -= $deductedQty;
                $remainingToDeduct = 0;
            } else {
                // This lot is partially fulfilling the deduction
                $deductedQty = $available;
                $lot->qty = 0;
                $remainingToDeduct -= $deductedQty;
            }

            $lot->updated_at = date('Y-m-d H:i:s');
            $lot->save(false);

            $deductions[] = [
                'lot_no' => $lot->lot_no,
                'qty' => $deductedQty
            ];
        }

        // If after looping we still have remaining qty, it means not enough stock in DB
        // But we must deduct it anyway (allow negative stock) or just put it on a blank lot.
        // Usually, the system should prevent issuing more than available, but if it allows:
        if ($remainingToDeduct > 0) {
            // Find or create a default/empty lot to put the negative balance
            $defaultLot = self::find()->where([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ])->andWhere(['OR', ['lot_no' => null], ['lot_no' => '']])->one();

            if (!$defaultLot) {
                $defaultLot = new self();
                $defaultLot->product_id = $productId;
                $defaultLot->warehouse_id = $warehouseId;
                $defaultLot->lot_no = null;
                $defaultLot->qty = 0;
                $defaultLot->reserv_qty = 0;
                $defaultLot->created_at = date('Y-m-d H:i:s');
            }

            $defaultLot->qty -= $remainingToDeduct;
            $defaultLot->updated_at = date('Y-m-d H:i:s');
            $defaultLot->save(false);

            $deductions[] = [
                'lot_no' => null,
                'qty' => $remainingToDeduct
            ];
        }

        self::updateProductStock($productId);

        return $deductions;
    }

    public static function updateStockIn($productId, $warehouseId, $qty, $stockType = null)
    {
        return self::updateStock($productId, $warehouseId, $qty, 1);
    }

    public static function updateStockOut($productId, $warehouseId, $qty, $stockType = null)
    {
        return self::updateStock($productId, $warehouseId, $qty, -1);
    }

    protected static function updateProductStock($productId): bool
    {
        $totalStock = self::find()
            ->where(['product_id' => $productId])->sum('qty');
        $product = Product::findOne($productId);
        if ($product) {
            $product->stock_qty = $totalStock ?: 0;
            $product->updated_at = date('Y-m-d H:i:s');
            $product->save(false);
        }
        return true;
    }
    public function beforeSave($insert){
        $this->company_id = (\Yii::$app->session->get('company_id') == 100 ? null : \Yii::$app->session->get('company_id'));
        return true;
    }

    /**
     * ดึงรายละเอียดสินค้าจาก JournalTransLine หรือ StockTrans ตาม lot_no
     * @param string $format 'html' หรือ 'text'
     * @return string
     */
    public function getLotDescription($format = 'html')
    {
        if (empty($this->lot_no)) {
            return '';
        }

        $lines = JournalTransLine::find()
            ->where(['product_id' => $this->product_id, 'lot_no' => $this->lot_no])
            ->all();

        $descriptions = [];
        if (!empty($lines)) {
            $isMulti = count($lines) > 1;
            foreach ($lines as $line) {
                $remark = trim($line->remark ?? '');
                if ($remark) {
                    $clean = preg_replace('/^NPR Detail #\d+\s*\|\s*/i', '', $remark);
                    $descItem = $clean;
                    if ($isMulti && $line->qty > 0) {
                        $descItem .= ' (' . number_format($line->qty, 0) . ')';
                    }
                    if (!in_array($descItem, $descriptions)) {
                        $descriptions[] = $descItem;
                    }
                }
            }
        }

        if (empty($descriptions)) {
            $trans = StockTrans::find()
                ->where(['product_id' => $this->product_id, 'lot_no' => $this->lot_no])
                ->andWhere(['!=', 'remark', ''])
                ->andWhere(['not', ['remark' => null]])
                ->all();
            foreach ($trans as $t) {
                $remark = trim($t->remark ?? '');
                if ($remark) {
                    $clean = preg_replace('/^NPR Detail #\d+\s*\|\s*/i', '', $remark);
                    if (!in_array($clean, $descriptions)) {
                        $descriptions[] = $clean;
                    }
                }
            }
        }

        if (empty($descriptions)) {
            return '';
        }

        if ($format === 'text') {
            return implode(' | ', $descriptions);
        }

        if (count($descriptions) > 1) {
            return implode('<br>', array_map(function($d) {
                return '<span class="text-secondary">• ' . \yii\helpers\Html::encode($d) . '</span>';
            }, $descriptions));
        }

        return \yii\helpers\Html::encode($descriptions[0]);
    }
}