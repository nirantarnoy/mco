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
            'timestampcdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                ],
                'value' => time(),
            ],
            'timestampudate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'updated_at',
                ],
                'value' => time(),
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
            'timestampupdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => time(),
            ],
        ];
    }

    public static function findCustomerData($quotation_id)
    {
        $data = [];
        $quotation = Quotation::find()->where(['id' => $quotation_id])->one();
        if ($quotation) {
            $customer_data = \backend\models\Customer::find()->where(['id' => $quotation->customer_id])->one();
            if ($customer_data) {
                array_push($data, [
                    'customer_id' => $customer_data->id,
                    'customer_name' => $customer_data->name,
                    'customer_address' => 'เลขที่ ' . $customer_data->home_number . ' ถนน ' . $customer_data->street . ' ซอย ' . $customer_data->aisle . ' ตำบล/แขวง ' . $customer_data->district_name . ' อําเภอ/เขต ' . $customer_data->city_name . ' จังหวัด ' . $customer_data->province_name . ' ' . $customer_data->zipcode,
                    'customer_tax_id' => $customer_data->taxid,
                    'invoice_due_date' => self::calDueDate($quotation->payment_term_id),
                ]);
            }
        }
        return $data;
    }

    public static function calDueDate($payment_term_id)
    {
        $due_date = null;
        if ($payment_term_id) {
            $payment_term = \backend\models\Paymentterm::find()->where(['id' => $payment_term_id])->one();
            if ($payment_term) {
                $due_date = $payment_term->day_count == 0 || $payment_term->day_count == null ? null : date('Y-m-d', strtotime('+' . $payment_term->day_count . ' day'));
            }
        }
        return $due_date;
    }

    public function getQuotation()
    {
        return $this->hasOne(Quotation::className(), ['id' => 'quotation_id']);
    }

    public static function findJobNo($id)
    {
        $model = \backend\models\Job::find()->where(['id' => $id])->one();
        return $model != null ? $model->job_no : '';
    }

    public static function generateJobNo()
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

    public static function getJobStatus($status = null)
    {
        $statuses = [
            self::JOB_STATUS_OPEN => 'Open',
            self::JOB_STATUS_CLOSED => 'Closed',
            self::JOB_STATUS_CANCELLED => 'Cancelled',
        ];
        return $statuses[$status] ?? 'ไม่ระบุ';
    }

    public static function getJobStatusBadge($status = null)
    {
        $badges = [
            self::JOB_STATUS_OPEN => '<span class="badge badge-warning">Open</span>',
            self::JOB_STATUS_CLOSED => '<span class="badge badge-success">Closed</span>',
            self::JOB_STATUS_CANCELLED => '<span class="badge badge-danger">Cancelled</span>',
        ];
        return $badges[$status] ?? '<span class="badge badge-secondary">ไม่ระบุ</span>';
    }

    /**
     * ความสัมพันธ์กับ JournalTrans
     */
    public function getJournalTrans()
    {
        return $this->hasMany(JournalTrans::class, ['job_id' => 'id']);
    }

    /**
     * ความสัมพันธ์กับ JournalTransLine ผ่าน JournalTrans
     */
    public function getJournalTransLines()
    {
        return $this->hasMany(JournalTransLine::class, ['journal_trans_id' => 'id'])
            ->via('journalTrans');
    }

    /**
     * คำนวณยอดรวมการเบิกของทั้งหมดของใบงาน
     */
    public function getTotalWithdrawAmount()
    {
        $total = 0;

        foreach ($this->journalTrans as $trans) {
            foreach ($trans->journalTransLines as $line) {
                // คำนวณจากราคาขาย (sale_price) คูณกับจำนวน (qty)
                $lineTotal = $line->sale_price * $line->qty;
                $total += $lineTotal;
            }
        }

        return $total;
    }

    /**
     * คำนวณกำไร/ขาดทุน
     */
    public function getProfitLoss()
    {
        return $this->job_amount - $this->getTotalWithdrawAmount();
    }

    /**
     * คำนวณเปอร์เซ็นต์กำไร/ขาดทุน
     */
    public function getProfitLossPercentage()
    {
        if ($this->job_amount <= 0) {
            return 0;
        }

        return ($this->getProfitLoss() / $this->job_amount) * 100;
    }

    /**
     * สถานะของงาน
     */
    public static function getStatusOptions()
    {
        return [
            '0' => 'รอดำเนินการ',
            '1' => 'กำลังดำเนินการ',
            '2' => 'เสร็จสิ้น',
            '3' => 'ยกเลิก',
        ];
    }

    /**
     * ข้อความสถานะ
     */
    public function getStatusText()
    {
        $options = self::getStatusOptions();
        return isset($options[$this->status]) ? $options[$this->status] : $this->status;
    }

    /**
     * สีแสดงสถานะ
     */
    public function getStatusColor()
    {
        switch ($this->status) {
            case 'pending':
                return 'warning';
            case 'in_progress':
                return 'info';
            case 'completed':
                return 'success';
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * สีแสดงกำไร/ขาดทุน
     */
    public function getProfitLossColor()
    {
        $profitLoss = $this->getProfitLoss();

        if ($profitLoss > 0) {
            return 'success'; // กำไร - สีเขียว
        } elseif ($profitLoss < 0) {
            return 'danger';  // ขาดทุน - สีแดง
        } else {
            return 'secondary'; // เท่ากัน - สีเทา
        }
    }
}
