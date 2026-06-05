<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class PreAdvanceRef extends ActiveRecord
{
    const REF_TYPE_NONE_PR = 1;

    public static function tableName()
    {
        return 'pre_advance_ref';
    }

    public function rules()
    {
        return [
            [['pre_advance_id', 'ref_id', 'ref_type'], 'integer'],
        ];
    }

    public function getPreAdvance()
    {
        return $this->hasOne(PreAdvance::class, ['id' => 'pre_advance_id']);
    }
}
