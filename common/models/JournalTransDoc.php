<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "journal_trans_doc".
 *
 * @property int $id
 * @property int|null $journal_trans_id
 * @property string|null $doc_name
 * @property string|null $note
 * @property int|null $created_at
 * @property int|null $created_by
 */
class JournalTransDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'journal_trans_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['journal_trans_id', 'doc_name', 'note', 'created_at', 'created_by'], 'default', 'value' => null],
            [['journal_trans_id', 'created_at', 'created_by'], 'integer'],
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
            'journal_trans_id' => 'Journal Trans ID',
            'doc_name' => 'Doc Name',
            'note' => 'Note',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

}
