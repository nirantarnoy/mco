<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "employer_doc".
 *
 * @property int $id
 * @property int|null $employer_id
 * @property string|null $doc
 * @property string|null $type
 */
class EmployerDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employer_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['employer_id', 'doc', 'type'], 'default', 'value' => null],
            [['employer_id'], 'integer'],
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
            'employer_id' => 'Employer ID',
            'doc' => 'Doc',
            'type' => 'Type',
        ];
    }

}
