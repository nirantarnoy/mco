<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vendor".
 *
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $vendor_group_id
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class Vendor extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name',], 'required'],
            [['name'], 'unique'],
            [['code', 'name', 'description', 'vendor_group_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['vendor_group_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['code', 'name', 'description'], 'string', 'max' => 255],
            [['taxid'],'string','max'=>13],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'รหัสผู้ขาย',
            'name' => 'ชื่อผู้ขาย',
            'description' => 'รายละเอียด',
            'vendor_group_id' => 'กลุ่มผู้ขาย',
            'status' => 'สถานะ',
            'taxid' => 'เลขประจําตัวผู้เสียภาษี',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
