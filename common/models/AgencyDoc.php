<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "agency_doc".
 *
 * @property int $id
 * @property int|null $agency_id
 * @property string|null $doc
 * @property string|null $type
 */
class AgencyDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agency_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['agency_id', 'doc', 'type'], 'default', 'value' => null],
            [['agency_id'], 'integer'],
            [['doc', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agency_id' => 'Agency ID',
            'doc' => 'Doc',
            'type' => 'Type',
        ];
    }

}
