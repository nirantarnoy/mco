<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "job_line".
 *
 * @property int $id
 * @property int|null $job_id
 * @property int|null $product_id
 * @property float|null $qty
 * @property float|null $line_price
 * @property float|null $line_total
 * @property int|null $status
 * @property string|null $note
 */
class JobLine extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'job_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'product_id', 'qty', 'line_price', 'line_total', 'status', 'note'], 'default', 'value' => null],
            [['job_id', 'product_id', 'status','unit_id'], 'integer'],
            [['qty', 'line_price', 'line_total'], 'number'],
            [['note'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'product_id' => 'Product ID',
            'qty' => 'Qty',
            'line_price' => 'Line Price',
            'line_total' => 'Line Total',
            'status' => 'Status',
            'note' => 'Note',
        ];
    }

    public function getProduct(){
        return $this->hasOne(\backend\models\Product::className(), ['id' => 'product_id']);
    }



}
