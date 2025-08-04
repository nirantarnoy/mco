<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "invoice_items".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $item_seq
 * @property string $item_description
 * @property float $quantity
 * @property string|null $unit
 * @property float $unit_price
 * @property float $amount
 * @property int|null $sort_order
 *
 * @property Invoice $invoice
 */
class InvoiceItem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id', 'item_description'], 'required'],
            [['invoice_id', 'item_seq', 'sort_order','product_id','unit_id'], 'integer'],
            [['item_description'], 'string'],
            [['quantity', 'unit_price', 'amount'], 'number', 'min' => 0],
            [['quantity'], 'default', 'value' => 1.000],
            [['unit_price', 'amount'], 'default', 'value' => 0.00],
            [['unit'], 'string', 'max' => 50],
            [['unit'], 'default', 'value' => 'หน่วย'],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_id' => 'Invoice ID',
            'item_seq' => 'ลำดับ',
            'item_description' => 'รายการ',
            'quantity' => 'จำนวน',
            'unit' => 'หน่วย',
            'unit_price' => 'ราคาต่อหน่วย',
            'amount' => 'จำนวนเงิน',
            'sort_order' => 'Sort Order',
            'product_id' => 'Product ID',
            'unit_id' => 'Unit ID',
        ];
    }

    /**
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

    /**
     * Calculate amount automatically
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calculate amount = quantity * unit_price
            $this->amount = $this->quantity * $this->unit_price;
            return true;
        }
        return false;
    }

    /**
     * After save, update invoice total
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->invoice) {
            $this->invoice->updateAmountsFromItems();
        }
    }

    /**
     * After delete, update invoice total
     */
    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->invoice) {
            $this->invoice->updateAmountsFromItems();
        }
    }
}