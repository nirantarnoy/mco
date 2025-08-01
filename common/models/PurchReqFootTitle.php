<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch_req_foot_title".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $status
 */
class PurchReqFootTitle extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_req_foot_title';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'status'], 'default', 'value' => null],
            [['status'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
        ];
    }

}
