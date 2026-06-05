<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class PreAdvanceDoc extends ActiveRecord
{
    public static function tableName()
    {
        return 'pre_advance_doc';
    }

    public function rules()
    {
        return [
            [['pre_advance_id', 'file_size', 'uploaded_at', 'uploaded_by'], 'integer'],
            [['file_name', 'file_path'], 'string', 'max' => 255],
        ];
    }

    public function getPreAdvance()
    {
        return $this->hasOne(PreAdvance::class, ['id' => 'pre_advance_id']);
    }
}
