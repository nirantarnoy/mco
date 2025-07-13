<?php
namespace backend\models;
use Yii;
use yii\db\ActiveRecord;
date_default_timezone_set('Asia/Bangkok');

class Job extends \common\models\Job
{
    const JOB_STATUS_OPEN = 1;
    const JOB_STATUS_CLOSED = 2;
    const JOB_STATUS_CANCELLED = 3;
    public function behaviors()
    {
        return [
            'timestampcdate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_at',
                ],
                'value'=> time(),
            ],
            'timestampudate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'updated_at',
                ],
                'value'=> time(),
            ],
//            'timestampcby'=>[
//                'class'=> \yii\behaviors\AttributeBehavior::className(),
//                'attributes'=>[
//                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_by',
//                ],
//                'value'=> Yii::$app->user->identity->id,
//            ],
//            'timestamuby'=>[
//                'class'=> \yii\behaviors\AttributeBehavior::className(),
//                'attributes'=>[
//                    ActiveRecord::EVENT_BEFORE_UPDATE=>'updated_by',
//                ],
//                'value'=> Yii::$app->user->identity->id,
//            ],
            'timestampupdate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_UPDATE=>'updated_at',
                ],
                'value'=> time(),
            ],
        ];
    }

    public function getQuotation(){
        return $this->hasOne(Quotation::className(), ['id' => 'quotation_id']);
    }

    public  static function generateJobNo()
    {
        $prefix = 'JO' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'job_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->job_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    public static function getJobStatus($status = null){
      $statuses = [
          self::JOB_STATUS_OPEN => 'Open',
          self::JOB_STATUS_CLOSED => 'Closed',
          self::JOB_STATUS_CANCELLED => 'Cancelled',
      ];
      return $statuses[$status] ?? 'ไม่ระบุ';
    }

    public static function getJobStatusBadge($status = null){
        $badges = [
            self::JOB_STATUS_OPEN => '<span class="badge badge-warning">Open</span>',
            self::JOB_STATUS_CLOSED => '<span class="badge badge-success">Closed</span>',
            self::JOB_STATUS_CANCELLED => '<span class="badge badge-danger">Cancelled</span>',
        ];
        return $badges[$status] ?? '<span class="badge badge-secondary">ไม่ระบุ</span>';
    }
}
