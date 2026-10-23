<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model class for table "user_module_permission".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property string $module_key
 * @property string $permission_type  'x' = เข้าไม่ได้, '0' = ดูอย่างเดียว, '1' = ทำรายการ
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class UserModulePermission extends ActiveRecord
{
    const PERM_NONE = 'x';    // เข้าไม่ได้
    const PERM_VIEW = '0';    // ดูอย่างเดียว
    const PERM_MANAGE = '1';  // ทำรายการ

    public static function tableName()
    {
        return '{{%user_module_permission}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'company_id', 'module_key'], 'required'],
            [['user_id', 'company_id', 'created_at', 'updated_at', 'updated_by'], 'integer'],
            [['module_key'], 'string', 'max' => 64],
            [['permission_type'], 'string', 'max' => 10],
            [['permission_type'], 'default', 'value' => self::PERM_NONE],
            [['user_id', 'company_id', 'module_key'], 'unique', 'targetAttribute' => ['user_id', 'company_id', 'module_key']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'ผู้ใช้งาน',
            'company_id' => 'บริษัท',
            'module_key' => 'รหัสโมดูล',
            'permission_type' => 'ประเภทสิทธิ์ (x/0/1)',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * ดึงสิทธิ์ทั้งหมดของแต่ละบริษัทออกมาในรูป [user_id => [module_key => permission_type]]
     *
     * @param int $companyId
     * @return array
     */
    public static function getPermissionsByCompany($companyId)
    {
        $rows = self::find()
            ->where(['company_id' => $companyId])
            ->asArray()
            ->all();

        $matrix = [];
        foreach ($rows as $r) {
            $matrix[$r['user_id']][$r['module_key']] = $r['permission_type'];
        }
        return $matrix;
    }
}
