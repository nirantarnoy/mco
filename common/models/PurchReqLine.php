<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch_req_line".
 *
 * @property int $id
 * @property int|null $purch_req_id
 * @property int|null $product_id
 * @property string|null $product_name
 * @property int|null $product_type
 * @property float|null $qty
 * @property float|null $line_price
 * @property float|null $line_total
 * @property int|null $status
 * @property string|null $note
 */
class PurchReqLine extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_req_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_req_id', 'product_id', 'product_name', 'product_type', 'qty', 'line_price', 'line_total', 'status', 'note'], 'default', 'value' => null],
            [['purch_req_id', 'product_id', 'product_type', 'status'], 'integer'],
            [['qty', 'line_price', 'line_total'], 'number'],
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
            'purch_req_id' => 'Purch Req ID',
            'product_id' => 'Product ID',
            'product_name' => 'Product Name',
            'product_type' => 'Product Type',
            'qty' => 'Qty',
            'line_price' => 'Line Price',
            'line_total' => 'Line Total',
            'status' => 'Status',
            'note' => 'Note',
        ];
    }

}
