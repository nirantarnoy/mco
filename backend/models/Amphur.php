<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "amphur".
 *
 * @property int $AMPHUR_ID
 * @property string $AMPHUR_CODE
 * @property string $AMPHUR_NAME
 * @property string $POSTCODE
 * @property int $GEO_ID
 * @property int $PROVINCE_ID
 */
class Amphur extends \common\models\Amphur
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'amphur';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['AMPHUR_CODE', 'AMPHUR_NAME', 'POSTCODE'], 'required'],
            [['GEO_ID', 'PROVINCE_ID'], 'integer'],
            [['AMPHUR_CODE'], 'string', 'max' => 4],
            [['AMPHUR_NAME'], 'string', 'max' => 150],
            [['POSTCODE'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'AMPHUR_ID' => 'Amphur ID',
            'AMPHUR_CODE' => 'Amphur Code',
            'AMPHUR_NAME' => 'Amphur Name',
            'POSTCODE' => 'Postcode',
            'GEO_ID' => 'Geo ID',
            'PROVINCE_ID' => 'Province ID',
        ];
    }

    public static function findAmphurName($id)
    {
        $model = Amphur::find()->where(['AMPHUR_ID' => $id])->one();
        return $model != null ? $model->AMPHUR_NAME : '';
    }

    public static function findCityzone($cityzone_id)
    {
        $city_member = '';
        $model = \common\models\CityzoneLine::find()->where(['cityzone_id' => $cityzone_id])->all();
        if ($model) {
            foreach ($model as $value) {
                $city_name = \backend\models\Amphur::findAmphurName($value->city_id);
                $city_member = $city_member . $city_name;
            }
        }
        return $city_member;
    }
}

