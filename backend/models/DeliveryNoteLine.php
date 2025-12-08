<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "delivery_note_line".
 *
 * @property int $id
 * @property int|null $delivery_note_id
 * @property string|null $item_no
 * @property string|null $description
 * @property string|null $part_no
 * @property float|null $qty
 * @property int|null $unit_id
 * @property string|null $remark
 *
 * @property DeliveryNote $deliveryNote
 */
class DeliveryNoteLine extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'delivery_note_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['delivery_note_id', 'unit_id'], 'integer'],
            [['description', 'remark'], 'string'],
            [['qty'], 'number'],
            [['item_no'], 'string', 'max' => 50],
            [['part_no'], 'string', 'max' => 255],
            [['delivery_note_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeliveryNote::class, 'targetAttribute' => ['delivery_note_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'delivery_note_id' => 'Delivery Note ID',
            'item_no' => 'Item',
            'description' => 'Description',
            'part_no' => 'P/N',
            'qty' => 'Q\'ty',
            'unit_id' => 'Unit',
            'remark' => 'Remark',
        ];
    }

    /**
     * Gets query for [[DeliveryNote]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryNote()
    {
        return $this->hasOne(DeliveryNote::class, ['id' => 'delivery_note_id']);
    }
    
    public function getUnit()
    {
        return $this->hasOne(Unit::class, ['id' => 'unit_id']);
    }
}
