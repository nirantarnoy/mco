<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "journal_trans_line".
 *
 * @property int $id
 * @property int $journal_trans_id
 * @property int $product_id
 * @property int $warehouse_id
 * @property float $qty
 * @property string $remark
 * @property string $status
 * @property float $line_price
 * @property string $return_to_type
 * @property float $sale_price
 * @property string $item_condition
 * @property string $condition_note
 * @property float $good_qty
 * @property float $damaged_qty
 * @property float $missing_qty
 *
 * @property JournalTrans $journalTrans
 * @property Product $product
 */
class JournalTransLine extends ActiveRecord
{
    // Return types for borrow returns
    const RETURN_TYPE_COMPLETE = 'complete';
    const RETURN_TYPE_DAMAGED = 'damaged';
    const RETURN_TYPE_INCOMPLETE = 'incomplete';

    // Item conditions for return borrow
    const CONDITION_GOOD = 'good';
    const CONDITION_DAMAGED = 'damaged';
    const CONDITION_MISSING = 'missing';

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
            [['journal_trans_id', 'product_id', 'warehouse_id', 'qty'], 'required'],
            [['journal_trans_id', 'product_id', 'warehouse_id'], 'integer'],
            [['qty', 'line_price', 'sale_price', 'good_qty', 'damaged_qty', 'missing_qty'], 'number'],
            [['remark', 'condition_note'], 'string'],
            [['status', 'return_to_type', 'item_condition','return_note'], 'string', 'max' => 255],
            [['return_to_type'], 'in', 'range' => [self::RETURN_TYPE_COMPLETE, self::RETURN_TYPE_DAMAGED, self::RETURN_TYPE_INCOMPLETE]],
            [['item_condition'], 'in', 'range' => [self::CONDITION_GOOD, self::CONDITION_DAMAGED, self::CONDITION_MISSING]],
            [['journal_trans_id'], 'exist', 'skipOnError' => true, 'targetClass' => JournalTrans::class, 'targetAttribute' => ['journal_trans_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['qty'], 'validateReturnQuantity'],
            [['good_qty', 'damaged_qty', 'missing_qty'], 'validateConditionQuantities'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'journal_trans_id' => 'Journal Trans ID',
            'product_id' => 'Product',
            'warehouse_id' => 'Warehouse',
            'qty' => 'Quantity',
            'remark' => 'Remark',
            'status' => 'Status',
            'line_price' => 'Line Price',
            'return_to_type' => 'Return Type',
            'sale_price' => 'Sale Price',
            'item_condition' => 'Item Condition',
            'condition_note' => 'Condition Note',
            'return_note' => 'Return Note',
            'good_qty' => 'Good Quantity',
            'damaged_qty' => 'Damaged Quantity',
            'missing_qty' => 'Missing Quantity',
        ];
    }

    /**
     * Get return type options
     */
    public static function getReturnTypeOptions()
    {
        return [
            self::RETURN_TYPE_COMPLETE => 'Complete/Good Condition',
            self::RETURN_TYPE_DAMAGED => 'Damaged',
            self::RETURN_TYPE_INCOMPLETE => 'Incomplete',
        ];
    }

    /**
     * Get item condition options
     */
    public static function getItemConditionOptions()
    {
        return [
            self::CONDITION_GOOD => 'Good Condition',
            self::CONDITION_DAMAGED => 'Damaged',
            self::CONDITION_MISSING => 'Missing',
        ];
    }

    /**
     * Validate return quantity against available return quantity
     */
    public function validateReturnQuantity($attribute, $params)
    {
        if ($this->journalTrans && in_array($this->journalTrans->trans_type_id, [JournalTrans::TRANS_TYPE_RETURN_ISSUE, JournalTrans::TRANS_TYPE_RETURN_BORROW])) {
            $availableQty = $this->journalTrans->getAvailableReturnQty($this->product_id);
            if ($this->$attribute > $availableQty) {
                $this->addError($attribute, "Return quantity ({$this->$attribute}) cannot exceed available quantity ({$availableQty}).");
            }
        }
    }

    /**
     * Validate condition quantities for return borrow
     */
    public function validateConditionQuantities($attribute, $params)
    {
        if ($this->isReturnBorrow()) {
            $totalConditionQty = ($this->good_qty ?: 0) + ($this->damaged_qty ?: 0) + ($this->missing_qty ?: 0);
            if ($totalConditionQty != $this->qty) {
                $this->addError($attribute, "Total condition quantities ({$totalConditionQty}) must equal return quantity ({$this->qty}).");
            }
        }
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

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert || $this->isAttributeChanged('product_id')) {
                // Get sale price from product
                $product = Product::findOne($this->product_id);
                if ($product) {
                    $this->sale_price = $product->sale_price;
                    $this->line_price = $this->qty * $this->sale_price;
                }
            }

            // Recalculate line price if quantity changed
            if ($this->isAttributeChanged('qty') && $this->sale_price) {
                $this->line_price = $this->qty * $this->sale_price;
            }

            // Auto-fill condition quantities for return borrow
            if ($this->isReturnBorrow() && empty($this->good_qty) && empty($this->damaged_qty) && empty($this->missing_qty)) {
                if ($this->return_to_type === self::RETURN_TYPE_COMPLETE) {
                    $this->good_qty = $this->qty;
                } elseif ($this->return_to_type === self::RETURN_TYPE_DAMAGED) {
                    $this->damaged_qty = $this->qty;
                } elseif ($this->return_to_type === self::RETURN_TYPE_INCOMPLETE) {
                    $this->missing_qty = $this->qty;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Check if this is a return borrow line
     */
    public function isReturnBorrow()
    {
        return $this->journalTrans && $this->journalTrans->trans_type_id == JournalTrans::TRANS_TYPE_RETURN_BORROW;
    }

    /**
     * Check if this is a return transaction line
     */
    public function isReturnTransaction()
    {
        return $this->journalTrans && in_array($this->journalTrans->trans_type_id, [
                JournalTrans::TRANS_TYPE_RETURN_ISSUE,
                JournalTrans::TRANS_TYPE_RETURN_BORROW
            ]);
    }

    /**
     * Validate return borrow
     */
    public function validateReturnBorrow()
    {
        if ($this->isReturnBorrow()) {
            if (empty($this->return_to_type)) {
                $this->addError('return_to_type', 'Return type is required for return borrow transaction.');
            }

            if (in_array($this->return_to_type, [self::RETURN_TYPE_DAMAGED, self::RETURN_TYPE_INCOMPLETE]) && empty($this->condition_note)) {
                $this->addError('condition_note', 'Condition note is required for damaged or incomplete items.');
            }

            // Validate condition quantities
            if ($this->good_qty === null && $this->damaged_qty === null && $this->missing_qty === null) {
                $this->addError('good_qty', 'At least one condition quantity is required.');
            }
        }
    }

    /**
     * After validate
     */
    public function afterValidate()
    {
        parent::afterValidate();
        $this->validateReturnBorrow();
    }

    /**
     * Get condition summary for display
     */
    public function getConditionSummary()
    {
        if (!$this->isReturnBorrow()) {
            return '-';
        }

        $summary = [];
        if ($this->good_qty > 0) {
            $summary[] = "Good: {$this->good_qty}";
        }
        if ($this->damaged_qty > 0) {
            $summary[] = "Damaged: {$this->damaged_qty}";
        }
        if ($this->missing_qty > 0) {
            $summary[] = "Missing: {$this->missing_qty}";
        }

        return implode(', ', $summary);
    }
}