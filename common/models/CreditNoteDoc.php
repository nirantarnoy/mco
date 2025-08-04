<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "credit_note_doc".
 *
 * @property int $id
 * @property int|null $credit_note_id
 * @property string|null $doc
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 */
class CreditNoteDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'credit_note_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['credit_note_id', 'doc', 'status', 'created_at', 'created_by'], 'default', 'value' => null],
            [['credit_note_id', 'status', 'created_at', 'created_by'], 'integer'],
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
            'credit_note_id' => 'Credit Note ID',
            'doc' => 'Doc',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

}
