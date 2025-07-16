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
            [['vendor_group_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by','is_head'], 'integer'],
            [['code', 'name', 'description','home_number', 'street', 'aisle', 'district_name', 'city_name', 'province_name', 'zipcode','contact_name','phone','branch_name', 'email'], 'string', 'max' => 255],
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
            'home_number' => 'บ้านเลขที่',
            'street' => 'ถนน',
            'aisle' => 'ซอย',
            'district_name' => 'ตําบล',
            'city_name' => 'อําเภอ',
            'province_name' => 'จังหวัด',
            'zipcode' => 'รหัสไปรษณีย์',
            'is_head' => 'สำนักงานใหญ่',
            'branch_name' => 'สาขา',
            'contact_name' => 'ชื่อผู้ติดต่อ',
            'phone' => 'โทรศัพท์',
            'email' => 'อีเมล',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
