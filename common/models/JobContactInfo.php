<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "job_contact_info".
 *
 * @property int $id
 * @property int|null $job_id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $phone
 * @property string|null $email
 */
class JobContactInfo extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'job_contact_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'name', 'description', 'phone', 'email'], 'default', 'value' => null],
            [['job_id'], 'integer'],
            [['name', 'description', 'phone', 'email'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'name' => 'Name',
            'description' => 'Description',
            'phone' => 'Phone',
            'email' => 'Email',
        ];
    }

}
