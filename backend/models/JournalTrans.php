<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "journal_trans".
 *
 * @property int $id
 * @property string $trans_date
 * @property string $journal_no
 * @property int $trans_type_id
 * @property int $stock_type_id
 * @property int $customer_id
 * @property string $customer_name
 * @property float $qty
 * @property string $remark
 * @property string $status
 * @property string $created_at
 * @property string $created_by
 * @property string $updated_at
 * @property string $updated_by
 * @property int $party_id
 * @property int $party_type_id
 * @property int $warehouse_id
 * @property string $trans_ref_id
 *
 * @property JournalTransLine[] $journalTransLines
 * @property StockTrans[] $stockTrans
 */
class JournalTrans extends ActiveRecord
{
    // Transaction Types
    const TRANS_TYPE_PO_RECEIVE = 1;
    const TRANS_TYPE_CANCEL_PO_RECEIVE = 2;
    const TRANS_TYPE_ISSUE_STOCK = 3;
    const TRANS_TYPE_RETURN_ISSUE = 4;
    const TRANS_TYPE_ISSUE_BORROW = 5;
    const TRANS_TYPE_RETURN_BORROW = 6;

    // Stock Types
    const STOCK_TYPE_IN = 1;
    const STOCK_TYPE_OUT = 2;

    // Status
    const STATUS_DRAFT = 0;
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_CANCELLED = 3;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public $journalTransLinesline = [];

    public $journalTransLines;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'journal_trans';
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
                'updatedAtAttribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['trans_date', 'trans_type_id', 'stock_type_id'], 'required'],
            [['trans_date', 'created_at', 'updated_at'], 'safe'],
            [['trans_type_id', 'stock_type_id', 'customer_id', 'party_id', 'party_type_id', 'warehouse_id','return_for_trans_id','trans_ref_id','status'], 'integer'],
            [['qty'], 'number'],
            [['remark'], 'string'],
            [['journal_no', 'customer_name', 'created_by', 'updated_by'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_CANCELLED]],
            [['trans_ref_id'], 'validateRefTransaction'],
        ];
    }

    /**
     * Validate reference transaction for return transactions
     */
    public function validateRefTransaction($attribute, $params)
    {
        if (in_array($this->trans_type_id, [self::TRANS_TYPE_RETURN_ISSUE, self::TRANS_TYPE_RETURN_BORROW])) {
            if (empty($this->$attribute)) {
                $this->addError($attribute, 'Reference transaction is required for return transactions.');
                return;
            }

            // Check if reference transaction exists and is approved
            $refTrans = self::find()->where(['journal_no' => $this->$attribute])->one();
            if (!$refTrans) {
                $this->addError($attribute, 'Reference transaction not found.');
                return;
            }

            if ($refTrans->status !== self::STATUS_APPROVED) {
                $this->addError($attribute, 'Reference transaction must be approved.');
                return;
            }

            // Check transaction type compatibility
            $validRefTypes = [];
            if ($this->trans_type_id == self::TRANS_TYPE_RETURN_ISSUE) {
                $validRefTypes = [self::TRANS_TYPE_ISSUE_STOCK];
            } elseif ($this->trans_type_id == self::TRANS_TYPE_RETURN_BORROW) {
                $validRefTypes = [self::TRANS_TYPE_ISSUE_BORROW];
            }

            if (!in_array($refTrans->trans_type_id, $validRefTypes)) {
                $this->addError($attribute, 'Invalid reference transaction type.');
                return;
            }
        }
    }

    /**
     * Get reference transaction
     */
    public function getRefTransaction()
    {
        if ($this->trans_ref_id) {
            return self::find()->where(['journal_no' => $this->trans_ref_id])->one();
        }
        return null;
    }

    /**
     * Get available quantity for return from reference transaction
     */
    public function getAvailableReturnQty($productId)
    {
        if (!$this->trans_ref_id) {
            return 0;
        }

        $refTrans = $this->getRefTransaction();
        if (!$refTrans) {
            return 0;
        }

        // Get issued quantity from reference transaction
        $issuedQty = 0;
        foreach ($refTrans->journalTransLines as $line) {
            if ($line->product_id == $productId) {
                $issuedQty += $line->qty;
            }
        }

        // Get already returned quantity
        $returnedQty = 0;
        $returnTransactions = self::find()
            ->where(['trans_ref_id' => $this->trans_ref_id])
            ->andWhere(['status' => self::STATUS_APPROVED])
            ->andWhere(['!=', 'id', $this->id]) // Exclude current transaction
            ->all();

        foreach ($returnTransactions as $returnTrans) {
            foreach ($returnTrans->journalTransLines as $line) {
                if ($line->product_id == $productId) {
                    $returnedQty += $line->qty;
                }
            }
        }

        return $issuedQty - $returnedQty;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trans_date' => 'วันที่',
            'journal_no' => 'เลขที่',
            'trans_type_id' => 'ประเภทรายการ',
            'stock_type_id' => 'ประเภทสต็อก',
            'customer_id' => 'รหัสลูกค้า',
            'customer_name' => 'ชื่อลูกค้า',
            'qty' => 'จํานวน',
            'remark' => 'หมายเหตุ',
            'status' => 'สถานะ',
            'return_for_trans_id' => 'เลขรายการอ้างอิง',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'party_id' => 'Party ID',
            'party_type_id' => 'Party Type ID',
            'warehouse_id' => 'คลังจัดเก็บ',
            'trans_ref_id' => 'Trans Ref ID',
        ];
    }

    /**
     * Get transaction type options
     */
    public static function getTransTypeOptions()
    {
        return [
            self::TRANS_TYPE_PO_RECEIVE => 'PO Receive',
            self::TRANS_TYPE_CANCEL_PO_RECEIVE => 'Cancel PO Receive',
            self::TRANS_TYPE_ISSUE_STOCK => 'Issue Stock',
            self::TRANS_TYPE_RETURN_ISSUE => 'Return Issue',
            self::TRANS_TYPE_ISSUE_BORROW => 'Issue Borrow',
            self::TRANS_TYPE_RETURN_BORROW => 'Return Borrow',
        ];
    }

    /**
     * Get stock type options
     */
    public static function getStockTypeOptions()
    {
        return [
            self::STOCK_TYPE_IN => 'Stock In',
            self::STOCK_TYPE_OUT => 'Stock Out',
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Generate running number
     */
    public function generateRunningNumber()
    {
        $transTypes = [
            self::TRANS_TYPE_PO_RECEIVE => 'POR',
            self::TRANS_TYPE_CANCEL_PO_RECEIVE => 'CPR',
            self::TRANS_TYPE_ISSUE_STOCK => 'ISS',
            self::TRANS_TYPE_RETURN_ISSUE => 'RIS',
            self::TRANS_TYPE_ISSUE_BORROW => 'IBR',
            self::TRANS_TYPE_RETURN_BORROW => 'RBR',
        ];

        $prefix = $transTypes[$this->trans_type_id] ?? 'TRN';
        $date = date('Ymd');

        // Find last number for this type and date
        $lastRecord = self::find()
            ->where(['trans_type_id' => $this->trans_type_id])
            ->andWhere(['like', 'journal_no', $prefix . $date])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->journal_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $this->journal_no = $prefix . $date . sprintf('%04d', $newNumber);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTransLines()
    {
        return $this->hasMany(JournalTransLine::class, ['journal_trans_id' => 'id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockTrans()
    {
        return $this->hasMany(StockTrans::class, ['journal_trans_id' => 'id']);
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->generateRunningNumber();
                $this->created_by = Yii::$app->user->identity->username ?? 'system';
                $this->status = self::STATUS_DRAFT;
            }
            $this->updated_by = Yii::$app->user->identity->username ?? 'system';
            return true;
        }
        return false;
    }

    /**
     * Check if transaction can be approved
     */
    public function canApprove()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Approve transaction
     */
    public function approve()
    {
        if ($this->canApprove()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->status = self::STATUS_APPROVED;
                $this->save(false);

                // Process stock movements
                $this->processStockMovements();

                $transaction->commit();
                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return false;
    }

    /**
     * Process stock movements
     */
    protected function processStockMovements()
    {
        foreach ($this->journalTransLines as $line) {
            // Create stock transaction
            $stockTrans = new StockTrans();
            $stockTrans->journal_trans_id = $this->id;
            $stockTrans->trans_date = $this->trans_date;
            $stockTrans->product_id = $line->product_id;
            $stockTrans->trans_type_id = $this->trans_type_id;
            $stockTrans->qty = $line->qty;
            $stockTrans->created_at = date('Y-m-d H:i:s');
            $stockTrans->created_by = $this->created_by;
            $stockTrans->status = 'completed';
            $stockTrans->remark = $line->remark;
            $stockTrans->stock_type_id = $this->stock_type_id;
            $stockTrans->warehouse_id = $this->warehouse_id;
            $stockTrans->line_price = $line->line_price;
            $stockTrans->updated_at = date('Y-m-d H:i:s');
            $stockTrans->save();

            // Update stock summary
            $this->updateStockSummary($line->product_id, $this->warehouse_id, $line->qty);
        }
    }

    /**
     * Update stock summary
     */
    protected function updateStockSummary($productId, $warehouseId, $qty)
    {
        $stockSum = StockSum::find()
            ->where(['product_id' => $productId, 'warehouse_id' => $warehouseId])
            ->one();

        if (!$stockSum) {
            $stockSum = new StockSum();
            $stockSum->product_id = $productId;
            $stockSum->warehouse_id = $warehouseId;
            $stockSum->qty = 0;
            $stockSum->reserve_qty = 0;
            $stockSum->created_at = date('Y-m-d H:i:s');
        }

        // Calculate quantity change based on stock type
        $qtyChange = ($this->stock_type_id == self::STOCK_TYPE_IN) ? $qty : -$qty;
        $stockSum->qty += $qtyChange;
        $stockSum->updated_at = date('Y-m-d H:i:s');
        $stockSum->save();

        // Update product total stock
        $this->updateProductTotalStock($productId);
    }

    /**
     * Update product total stock
     */
    protected function updateProductTotalStock($productId)
    {
        $totalStock = StockSum::find()
            ->where(['product_id' => $productId])
            ->sum('qty');

        $product = Product::findOne($productId);
        if ($product) {
            $product->stock_qty = $totalStock ?: 0;
            $product->updated_at = date('Y-m-d H:i:s');
            $product->save(false);
        }
    }
}