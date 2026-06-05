<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class PreAdvanceLine extends ActiveRecord
{
    public static function tableName()
    {
        return 'pre_advance_line';
    }

    public function rules()
    {
        return [
            [['pre_advance_id'], 'integer'],
            [['amount'], 'number'],
            [['line_date'], 'safe'],
            [['description'], 'string', 'max' => 255],
            [['remark'], 'string'],
        ];
    }

    public function getPreAdvance()
    {
        return $this->hasOne(PreAdvance::class, ['id' => 'pre_advance_id']);
    }
}
