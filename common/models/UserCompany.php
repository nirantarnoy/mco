<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model class for table "user_company".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property int $status
 * @property int|null $created_at
 * @property int|null $created_by
 */
class UserCompany extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_company}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'company_id'], 'required'],
            [['user_id', 'company_id', 'status', 'created_at', 'created_by'], 'integer'],
            [['status'], 'default', 'value' => 1],
            [['user_id', 'company_id'], 'unique', 'targetAttribute' => ['user_id', 'company_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'ผู้ใช้งาน',
            'company_id' => 'บริษัท',
            'status' => 'สถานะ',
            'created_at' => 'สร้างเมื่อ',
            'created_by' => 'สร้างโดย',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(\backend\models\Company::class, ['id' => 'company_id']);
    }

    /**
     * ดึง ID บริษัททั้งหมดที่ User มีสิทธิ์เข้าใช้งาน
     *
     * @param int $userId
     * @return array
     */
    public static function getUserCompanyIds($userId)
    {
        $ids = self::find()
            ->select('company_id')
            ->where(['user_id' => $userId, 'status' => 1])
            ->column();

        return array_map('intval', $ids);
    }

    /**
     * ตรวจสอบว่า User มีสิทธิ์ในบริษัทนี้หรือไม่
     *
     * @param int $userId
     * @param int $companyId
     * @return bool
     */
    public static function hasAccess($userId, $companyId)
    {
        // System admin เข้าได้ทุกบริษัท
        if (\backend\models\User::isUserAdmin($userId)) {
            return true;
        }

        return self::find()
            ->where(['user_id' => $userId, 'company_id' => $companyId, 'status' => 1])
            ->exists();
    }
}
