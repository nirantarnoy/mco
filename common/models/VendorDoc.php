<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vendor_doc".
 *
 * @property int $id
 * @property int|null $vendor_id
 * @property string|null $doc_name
 * @property string|null $note
 * @property int|null $created_at
 * @property int|null $created_by
 */
class VendorDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', 'doc_name', 'note', 'created_at', 'created_by'], 'default', 'value' => null],
            [['vendor_id', 'created_at', 'created_by'], 'integer'],
            [['doc_name', 'note'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vendor_id' => 'Vendor ID',
            'doc_name' => 'Doc Name',
            'note' => 'Note',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

}
