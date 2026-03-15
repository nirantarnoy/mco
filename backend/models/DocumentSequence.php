<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "document_sequence".
 *
 * @property int $id
 * @property string $document_type
 * @property string|null $prefix
 * @property int|null $last_year
 * @property int|null $last_number
 */
class DocumentSequence extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_sequence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['document_type'], 'required'],
            [['last_year', 'last_number'], 'integer'],
            [['document_type', 'prefix'], 'string', 'max' => 50],
            [['document_type'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'document_type' => 'Document Type',
            'prefix' => 'Prefix',
            'last_year' => 'Last Year',
            'last_number' => 'Last Number',
        ];
    }
}
