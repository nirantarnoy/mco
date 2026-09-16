<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "system_setting".
 *
 * @property int $id
 * @property string $setting_key
 * @property string|null $setting_value
 * @property string|null $description
 * @property int|null $updated_at
 */
class SystemSetting extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'system_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['setting_key'], 'required'],
            [['setting_value'], 'string'],
            [['updated_at'], 'integer'],
            [['setting_key'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 255],
            [['setting_key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'setting_key' => 'Setting Key',
            'setting_value' => 'Setting Value',
            'description' => 'Description',
            'updated_at' => 'Updated At',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->updated_at = time();
            return true;
        }
        return false;
    }

    /**
     * Gets a setting value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::findOne(['setting_key' => $key]);
        return $setting ? $setting->setting_value : $default;
    }
}
