<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "worker_doc".
 *
 * @property int $id
 * @property int|null $worker_id
 * @property string|null $doc
 * @property string|null $type
 */
class WorkerDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'worker_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['worker_id', 'doc', 'type'], 'default', 'value' => null],
            [['worker_id'], 'integer'],
            [['doc', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'worker_id' => 'Worker ID',
            'doc' => 'Doc',
            'type' => 'Type',
        ];
    }

}
