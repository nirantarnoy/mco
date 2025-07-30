<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $customer_group_id
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class Customer extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name',], 'required'],
            [['name',], 'unique'],
            [['name', 'description', 'customer_group_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['customer_group_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by','is_head'], 'integer'],
            [['name', 'description','home_number', 'street', 'aisle', 'district_name', 'city_name', 'province_name', 'zipcode','contact_name','phone','branch_name', 'email','code'], 'string', 'max' => 255],
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
            'name' => 'ชื่อลูกค้า',
            'description' => 'รายละเอียด',
            'customer_group_id' => 'กลุ่มลูกค้า',
            'status' => 'สถานะ',
            'taxid' => 'เลขประจําตัวผู้เสียภาษี',
            'home_number' => 'บ้านเลขที่',
            'street' => 'ถนน',
            'aisle' => 'ซอย',
            'district_name' => 'แขวง/ตําบล',
            'city_name' => 'เขต/อําเภอ',
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
