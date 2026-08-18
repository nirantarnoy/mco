<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "job_activity_status".
 *
 * @property int $id
 * @property int $job_id
 * @property int $step_no
 * @property int $status 0: Red, 1: Orange, 2: Green, 3: Cancelled
 * @property string|null $remarks
 * @property int|null $cancelled_by
 * @property int|null $cancelled_at
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class JobActivityStatus extends ActiveRecord
{
    const STATUS_RED = 0;
    const STATUS_ORANGE = 1;
    const STATUS_GREEN = 2;
    const STATUS_CANCELLED = 3;

    public static function tableName()
    {
        return 'job_activity_status';
    }

    public function rules()
    {
        return [
            [['job_id', 'step_no'], 'required'],
            [['job_id', 'step_no', 'status', 'cancelled_by', 'cancelled_at', 'created_at', 'updated_at'], 'integer'],
            [['remarks'], 'string'],
        ];
    }

    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }

    public static function getStatusLabel($status)
    {
        switch ($status) {
            case self::STATUS_GREEN:
                return '<span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i> เรียบร้อยแล้ว (Green)</span>';
            case self::STATUS_ORANGE:
                return '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> กำลังดำเนินการ (Orange)</span>';
            case self::STATUS_CANCELLED:
                return '<span class="badge bg-secondary text-white"><i class="fas fa-ban me-1"></i> ยกเลิก</span>';
            case self::STATUS_RED:
            default:
                return '<span class="badge bg-danger text-white"><i class="fas fa-exclamation-triangle me-1"></i> ยังไม่ได้ดำเนินการ (Red)</span>';
        }
    }
}
