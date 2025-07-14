<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "district".
 *
 * @property int $DISTRICT_ID
 * @property string $DISTRICT_CODE
 * @property string $DISTRICT_NAME
 * @property int $AMPHUR_ID
 * @property int $PROVINCE_ID
 * @property int $GEO_ID
 */
class District extends \common\models\District
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'district';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['DISTRICT_CODE', 'DISTRICT_NAME'], 'required'],
            [['AMPHUR_ID', 'PROVINCE_ID', 'GEO_ID'], 'integer'],
            [['DISTRICT_CODE'], 'string', 'max' => 6],
            [['DISTRICT_NAME'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'DISTRICT_ID' => 'District ID',
            'DISTRICT_CODE' => 'District Code',
            'DISTRICT_NAME' => 'District Name',
            'AMPHUR_ID' => 'Amphur ID',
            'PROVINCE_ID' => 'Province ID',
            'GEO_ID' => 'Geo ID',
        ];
    }

    public static function findDistrictName($id)
    {
        $model = District::find()->where(['DISTRICT_ID' => $id])->one();
        return $model != null ? $model->DISTRICT_NAME : '';
    }
}
