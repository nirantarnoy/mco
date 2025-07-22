<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "agency".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $phone
 * @property string|null $idcard_no
 * @property string|null $doc
 * @property int|null $status
 * @property int|null $emp_id
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class Agency extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'],'required'],
            [['name', 'description', 'phone', 'idcard_no', 'doc', 'status', 'emp_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['status', 'emp_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'description', 'phone', 'idcard_no', 'doc'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อหน่วยงาน',
            'description' => 'รายละเอียด',
            'phone' => 'เบอร์โทรศัพท์',
            'idcard_no' => 'เลขบัตรประชาชน',
            'doc' => 'ไฟล์เอกสาร',
            'status' => 'สถานะ',
            'emp_id' => 'ผู้ติดต่อหน่วยงาน',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
