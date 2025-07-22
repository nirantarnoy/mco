<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "department".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $create_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class Department extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'department';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'],'required'],
            [['name', 'description', 'status', 'created_at', 'create_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['status', 'created_at', 'create_by', 'updated_at', 'updated_by',], 'integer'],
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
            'name' => 'ชื่อ',
            'description' => 'รายละเอียด',
            'status' => 'สถานะ',
          
            'created_at' => 'Created At',
            'create_by' => 'Create By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
