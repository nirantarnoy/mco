<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "invoice_doc".
 *
 * @property int $id
 * @property int|null $invoice_id
 * @property string|null $doc
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 */
class InvoiceDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id', 'doc', 'status', 'created_at', 'created_by'], 'default', 'value' => null],
            [['invoice_id', 'status', 'created_at', 'created_by'], 'integer'],
            [['doc'], 'string', 'max' => 255],
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
            'doc' => 'Doc',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

}
