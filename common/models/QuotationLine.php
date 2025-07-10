<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quotation_line".
 *
 * @property int $id
 * @property int|null $quotation_id
 * @property int|null $product_id
 * @property string|null $product_name
 * @property float|null $qty
 * @property float|null $line_price
 * @property float|null $line_total
 * @property float|null $discount_amount
 * @property int|null $status
 * @property string|null $note
 */
class QuotationLine extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quotation_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quotation_id', 'product_id', 'product_name', 'qty', 'line_price', 'line_total', 'discount_amount', 'status', 'note'], 'default', 'value' => null],
            [['quotation_id', 'product_id', 'status'], 'integer'],
            [['qty', 'line_price', 'line_total', 'discount_amount'], 'number'],
            [['product_name', 'note'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quotation_id' => 'Quotation ID',
            'product_id' => 'Product ID',
            'product_name' => 'Product Name',
            'qty' => 'Qty',
            'line_price' => 'Line Price',
            'line_total' => 'Line Total',
            'discount_amount' => 'Discount Amount',
            'status' => 'Status',
            'note' => 'Note',
        ];
    }

}
