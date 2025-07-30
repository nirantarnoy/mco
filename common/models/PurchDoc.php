<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch_doc".
 *
 * @property int $id
 * @property int|null $purch_id
 * @property string|null $doc_name
 * @property string|null $note
 * @property int|null $created_at
 * @property int|null $created_by
 */
class PurchDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_id', 'doc_name', 'note', 'created_at', 'created_by'], 'default', 'value' => null],
            [['purch_id', 'created_at', 'created_by'], 'integer'],
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
            'purch_id' => 'Purch ID',
            'doc_name' => 'Doc Name',
            'note' => 'Note',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

}
