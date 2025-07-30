<?php

namespace common\models;

use backend\models\JournalTrans;
use Yii;

/**
 * This is the model class for table "journal_trans_aricat".
 *
 * @property int $id
 * @property int|null $journal_trans_id
 * @property int|null $worker_id
 */
class JournalTransAricat extends \yii\db\ActiveRecord
{
  public $worker_name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'journal_trans_aricat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['journal_trans_id', 'worker_id'], 'default', 'value' => null],
            [['journal_trans_id', 'worker_id'], 'integer'],
            [['note'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'journal_trans_id' => 'Journal Trans ID',
            'worker_id' => 'Worker ID',
            'note' => 'Note',
        ];
    }

    public function getJournalTrans()
    {
        return $this->hasOne(JournalTrans::class, ['id' => 'journal_trans_id']);
    }

    public function getWorker(){
        return $this->hasOne(Worker::class, ['id' => 'worker_id']);
    }

}
