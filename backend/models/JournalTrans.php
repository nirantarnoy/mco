<?php

namespace backend\models;

use common\models\JournalTransAricat;
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
 * @property int $status
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

    const TRANS_TYPE_ARICAT_NEW = 7;

    const TRANS_TYPE_ADJUST_STOCK = 8;

    // Stock Types
    const STOCK_TYPE_IN = 1;
    const STOCK_TYPE_OUT = 2;

    // Status
    const STATUS_DRAFT = 0;
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_CANCELLED = 3;

    const STATUS_ACTIVE = 100;
    const STATUS_INACTIVE = 0;

    const ISSUE_TRANS_WAITING = 0;
    const ISSUE_TRANS_APPROVED = 1;
    const ISSUE_TRANS_CANCELLED = 2;

    public $journalTransLinesline = [];

    public $journalTransLinesaricat = [];

    //  public $journalTransLines;

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
            [['trans_date', 'created_at', 'updated_at', 'approve_date'], 'safe'],
            [['trans_type_id', 'stock_type_id', 'job_id', 'customer_id', 'party_id', 'party_type_id', 'warehouse_id', 'return_for_trans_id', 'trans_ref_id', 'status', 'created_by', 'updated_by', 'po_rec_status'], 'integer'],
            [['qty',], 'number'],
            [['remark'], 'string'],
            [['journal_no', 'customer_name',], 'string', 'max' => 255],
            //   [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_CANCELLED, self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            //  [['status'], 'in', 'range' => [self::STATUS_ACTIVE]],
            [['return_for_trans_id'], 'validateRefTransaction'],
            [['agency_id', 'employer_id', 'emp_trans_id', 'approve_by'], 'integer'],
        ];
    }

    /**
     * Validate reference transaction for return transactions
     */
    public function validateRefTransaction($attribute, $params)
    {
        if (in_array($this->trans_type_id, [self::TRANS_TYPE_RETURN_ISSUE, self::TRANS_TYPE_RETURN_BORROW])) {
            if (empty($this->return_for_trans_id)) {
                $this->addError($attribute, 'Reference transaction is required for return transactions.');
                return;
            }

            // Check if reference transaction exists and is approved
            $refTrans = self::find()->where(['id' => $this->return_for_trans_id])->one();
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
        if ($this->return_for_trans_id) {
            return self::find()->where(['id' => $this->return_for_trans_id])->one();
        }
        return null;
    }

    /**
     * Get available quantity for return from reference transaction
     */
    public function getAvailableReturnQty($productId)
    {
        if (!$this->return_for_trans_id) {
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
            ->where(['return_for_trans_id' => $this->return_for_trans_id])
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
            'job_id' => 'เลขที่ใบงาน',
            'return_for_trans_id' => 'เลขรายการอ้างอิง',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'party_id' => 'Party ID',
            'party_type_id' => 'Party Type ID',
            'warehouse_id' => 'คลังจัดเก็บ',
            'trans_ref_id' => 'Trans Ref ID',
            'agency_id' => 'หน่วยงาน',
            'employer_id' => 'นายจ้าง',
            'emp_trans_id' => 'ผู้เบิก/ผู้คืน',
            'approve_date' => 'วันที่อนุมัติ',
            'approve_by' => 'ผู้อนุมัติ',

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
            self::TRANS_TYPE_ARICAT_NEW => 'Aricat',
            self::TRANS_TYPE_ADJUST_STOCK => 'Adjust Stock',
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

    public static function getIssueTransStatus()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
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
            self::TRANS_TYPE_ARICAT_NEW => 'ARC',
            self::TRANS_TYPE_ADJUST_STOCK => 'AIN',
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
    /**
     * ความสัมพันธ์กับ JournalTransLine
     */
    public function getJournalTransLines()
    {
        return $this->hasMany(JournalTransLine::class, ['journal_trans_id' => 'id']);
    }

    public function getJournalTransAricat()
    {
        return $this->hasMany(JournalTransAricat::class, ['journal_trans_id' => 'id']);
    }

    /**
     * คำนวณยอดรวมของ JournalTrans
     */
    public function getTotalAmount()
    {
        $total = 0;
        foreach ($this->journalTransLines as $line) {
            $total += $line->sale_price * $line->qty;
        }
        return $total;
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
                $this->created_by = Yii::$app->user->id;// Yii::$app->user->identity->username ?? 'system';
             //   $this->emp_trans_id = Yii::$app->user->id;
                $this->status = self::STATUS_DRAFT;
            }
            $this->updated_by = Yii::$app->user->id;
           // $this->emp_trans_id = Yii::$app->user->id;
            $this->company_id = \Yii::$app->session->get('company_id');
            return true;
        }


        return false;
    }

    /**
     * Check if transaction can be approved
     */
    public function canApprove()
    {
        return $this->status === self::STATUS_DRAFT; // self::STATUS_PENDING;
    }

    /**
     * Approve transaction
     */
    public function approve()
    {
        if ($this->canApprove()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Validate stock availability for outbound transactions
                if ($this->stock_type_id == self::STOCK_TYPE_OUT) {
                    $checkStock = [];
                    foreach ($this->journalTransLines as $line) {
                        if ($line->status == JournalTransLine::STATUS_CANCELLED) continue;
                        $key = $line->product_id . '_' . $line->warehouse_id;
                        if (!isset($checkStock[$key])) {
                            $checkStock[$key] = [
                                'product_name' => $line->product->name ?? 'Unknown',
                                'qty' => 0,
                                'product_model' => $line->product,
                                'warehouse_id' => $line->warehouse_id
                            ];
                        }
                        $checkStock[$key]['qty'] += $line->qty;
                    }

                    foreach ($checkStock as $check) {
                        if ($check['product_model']) {
                            $availableStock = $check['product_model']->getAvailableStockInWarehouse($check['warehouse_id']);
                            if ($availableStock < $check['qty']) {
                                throw new \Exception("Insufficient stock for product: {$check['product_name']}. Available: {$availableStock}, Required: {$check['qty']}");
                            }
                        }
                    }
                }

                $this->status = self::STATUS_APPROVED;
                $this->approve_by = \Yii::$app->user->id;
                $this->approve_date = date('Y-m-d H:i:s');
                if (!$this->save(false)) {
                    throw new \Exception("Failed to save transaction status.");
                }

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
     * Cancel the entire transaction and reverse all stock movements.
     */
    public function cancel()
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->journalTransLines as $line) {
                if ($line->status != JournalTransLine::STATUS_CANCELLED) {
                    $this->cancelLineInternal($line);
                }
            }

            // Update main transaction status
            $this->status = self::STATUS_CANCELLED;
            $this->updated_by = Yii::$app->user->id;
            $this->updated_at = date('Y-m-d H:i:s');
            if (!$this->save(false)) {
                throw new \Exception("Failed to update transaction status.");
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Cancel a single line item.
     */
    public function cancelLine($lineId)
    {
        $line = JournalTransLine::findOne($lineId);
        if (!$line || $line->journal_trans_id != $this->id) {
            return false;
        }

        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        if ($line->status == JournalTransLine::STATUS_CANCELLED) {
            return true;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->cancelLineInternal($line);
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    protected function cancelLineInternal($line)
    {
        $reverseDirection = ($this->stock_type_id == self::STOCK_TYPE_IN) ? -1 : 1;
        $reverseStockTypeId = ($this->stock_type_id == self::STOCK_TYPE_IN) ? self::STOCK_TYPE_OUT : self::STOCK_TYPE_IN;

        $qtyToReverse = $line->qty;
        // สำหรับรายการคืน จะต้อง reverse เฉพาะยอดดีที่รับเข้าไปจริง
        if (in_array($this->trans_type_id, [self::TRANS_TYPE_RETURN_ISSUE, self::TRANS_TYPE_RETURN_BORROW])) {
            if ($line->good_qty !== null) {
                $qtyToReverse = (float)$line->good_qty;
            } elseif ($line->is_damage == 2) { // 2 = สภาพไม่ปกติ
                $qtyToReverse = 0;
            }
        }

        if ($qtyToReverse > 0) {
            // ตรวจสอบสต็อกก่อนหักออก (กรณี reverseDirection เป็น -1 คือการรับเข้าแล้วจะกดยกเลิกเพื่อถอนออก)
            if ($reverseDirection == -1) {
                $stockSum = StockSum::find()->where(['product_id' => $line->product_id, 'warehouse_id' => $line->warehouse_id])->one();
                $available = $stockSum ? $stockSum->qty : 0;
                if ($available < $qtyToReverse) {
                    throw new \Exception("ไม่สามารถยกเลิกรายการได้: สินค้า " . ($line->product->name ?? '') . " ในคลังมีไม่เพียงพอสำหรับการหักออก (ต้องการ: {$qtyToReverse}, คงเหลือ: {$available})");
                }
            }

            // Update Stock Sum
            StockSum::updateStock($line->product_id, $line->warehouse_id, $qtyToReverse, $reverseDirection);
        }

        // Create reverse StockTrans for history
        $stockTrans = new StockTrans();
        $stockTrans->journal_trans_id = $this->id;
        $stockTrans->product_id = $line->product_id;
        $stockTrans->warehouse_id = $line->warehouse_id;
        $stockTrans->qty = $qtyToReverse;
        $stockTrans->stock_type_id = $reverseStockTypeId;
        $stockTrans->trans_type_id = $this->trans_type_id;
        $stockTrans->trans_date = date('Y-m-d H:i:s');
        $stockTrans->created_at = date('Y-m-d H:i:s');
        $stockTrans->created_by = Yii::$app->user->id;
        $stockTrans->status = StockTrans::STATUS_CANCELLED;
        $stockTrans->remark = "ยกเลิกรายการ: " . ($line->product->code ?? '');
        $stockTrans->line_price = $line->line_price;
        
        if ($qtyToReverse > 0) {
            if (!$stockTrans->save(false)) {
                throw new \Exception("Failed to save stock transaction history.");
            }
        }

        $line->status = JournalTransLine::STATUS_CANCELLED;
        if (!$line->save(false)) {
            throw new \Exception("Failed to update line status.");
        }
    }

    /**
     * Process stock movements
     */
    protected function processStockMovements()
    {
        foreach ($this->journalTransLines as $line) {
            $qtyToUpdate = $line->qty;

            // สำหรับรายการคืน (คืนจากการเบิก หรือ คืนจากการยืม)
            // ให้รับคืนเข้าคลังเฉพาะยอด "ของดี" (good_qty) เท่านั้น
            if (in_array($this->trans_type_id, [self::TRANS_TYPE_RETURN_ISSUE, self::TRANS_TYPE_RETURN_BORROW])) {
                if ($line->good_qty !== null) {
                    $qtyToUpdate = (float)$line->good_qty;
                } elseif ($line->is_damage == 2) { // 2 = สภาพไม่ปกติ (ตามที่ระบุใน _form.php)
                    $qtyToUpdate = 0;
                }
            }

            if ($qtyToUpdate > 0) {
                // Create stock transaction for the actual stock increase (Good items)
                $stockTrans = new StockTrans();
                $stockTrans->journal_trans_id = $this->id;
                $stockTrans->trans_date = $this->trans_date;
                $stockTrans->product_id = $line->product_id;
                $stockTrans->trans_type_id = $this->trans_type_id;
                $stockTrans->qty = $qtyToUpdate;
                $stockTrans->created_at = date('Y-m-d H:i:s');
                $stockTrans->created_by = $this->created_by;
                $stockTrans->status = 3; // Completed
                $stockTrans->remark = $line->remark;
                $stockTrans->stock_type_id = $this->stock_type_id;
                $stockTrans->warehouse_id = $line->warehouse_id; 
                $stockTrans->line_price = $line->line_price;
                $stockTrans->updated_at = date('Y-m-d H:i:s');
                if (!$stockTrans->save(false)) {
                    Yii::error("Failed to save StockTrans: " . print_r($stockTrans->errors, true));
                }

                // Update stock summary
                $this->updateStockSummary($line->product_id, $line->warehouse_id, $qtyToUpdate);
            }
            
            // กรณีมีของเสีย หรือ ของหาย ให้บันทึกเป็น StockTrans เฉพาะรายการ (ถ้าต้องการเก็บประวัติ)
            // แต่จะไม่ไปยุ่งกับ StockSum (เพราะของเสีย/หาย ไม่ควรเป็นสต็อกที่ใช้ได้)
            if (in_array($this->trans_type_id, [self::TRANS_TYPE_RETURN_ISSUE, self::TRANS_TYPE_RETURN_BORROW])) {
                if ($line->damaged_qty > 0) {
                    $damagedTrans = new StockTrans();
                    $damagedTrans->journal_trans_id = $this->id;
                    $damagedTrans->trans_date = $this->trans_date;
                    $damagedTrans->product_id = $line->product_id;
                    $damagedTrans->trans_type_id = $this->trans_type_id;
                    $damagedTrans->qty = $line->damaged_qty;
                    $damagedTrans->status = 0; // Draft or a specific status for non-stock items
                    $damagedTrans->remark = "[ของเสีย] " . $line->remark;
                    $damagedTrans->stock_type_id = $this->stock_type_id;
                    $damagedTrans->warehouse_id = $line->warehouse_id;
                    $damagedTrans->save(false);
                }
                if ($line->missing_qty > 0) {
                    $missingTrans = new StockTrans();
                    $missingTrans->journal_trans_id = $this->id;
                    $missingTrans->trans_date = $this->trans_date;
                    $missingTrans->product_id = $line->product_id;
                    $missingTrans->trans_type_id = $this->trans_type_id;
                    $missingTrans->qty = $line->missing_qty;
                    $missingTrans->status = 0; // Draft or a specific status for non-stock items
                    $missingTrans->remark = "[ของหาย] " . $line->remark;
                    $missingTrans->stock_type_id = $this->stock_type_id;
                    $missingTrans->warehouse_id = $line->warehouse_id;
                    $missingTrans->save(false);
                }
            }
        }
    }

    /**
     * Update stock summary
     */
    protected function updateStockSummary($productId, $warehouseId, $qty)
    {
        $direction = ($this->stock_type_id == self::STOCK_TYPE_IN) ? 1 : -1;
        StockSum::updateStock($productId, $warehouseId, $qty, $direction);
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

    /**
     * ความสัมพันธ์กับ Job
     */
    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }

    public static function findJournalNoFromStockTransId($stockTransId)
    {
        $stockTrans = JournalTrans::findOne($stockTransId);
        if ($stockTrans) {
            return $stockTrans->journal_no;
        }
        return null;
    }
}