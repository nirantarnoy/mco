<?php

namespace backend\models;

use common\models\JobLine;
use Yii;
use yii\db\ActiveRecord;

date_default_timezone_set('Asia/Bangkok');

class Job extends \common\models\Job
{
    private $_activityCache = [];
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

    public function getJobLines()
    {
        return $this->hasMany(JobLine::className(), ['job_id' => 'id']);
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
        return $this->job_amount - ($this->getTotalWithdrawAmount() + $this->getJobexpenseAll() + $this->getVehicleExpenseAll());
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
            case '0':
                return 'secondary';
            case '1':
                return 'warning';
            case '2':
                return 'success';
            case '3':
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


    ///// get new
    public function getHasPurchaseRequest()
    {
        return $this->hasPurchaseRequest($this->id);
    }
    public function getHasPurchaseOrder()
    {
        return $this->hasPurchaseOrder($this->id);
    }
    public function getHasReceiveTransaction(){
        return $this->hasReceiveTransaction($this->id);
    }



    /**
     * ตรวจสอบว่ามีใบขอซื้อหรือไม่
     * @return bool
     */
//    public function hasPurchaseRequest($id)
//    {
//        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM purch_req pr INNER JOIN purch_req_doc prd ON prd.purch_req_id = pr.id WHERE pr.job_id = :jobId')
//            ->bindParam(':jobId', $id)
//            ->queryScalar();
//        return $count > 0;
//    }

    public function hasPurchaseRequest($id)
    {
        return $this->cacheActivity('purchase_request', function () use ($id) {
            $db = Yii::$app->db;

            // ดึงจำนวนใบขอซื้อทั้งหมด
            $total = $db->createCommand("
            SELECT COUNT(*) 
            FROM purch_req 
            WHERE job_id = :jobId
        ")
                ->bindValue(':jobId', $id)
                ->queryScalar();

            // ถ้าไม่มีใบขอซื้อเลย
            if ($total == 0) {
                return 0;
            }

            // ดึงจำนวนใบที่มีไฟล์แนบ
            $complete = $db->createCommand("
            SELECT COUNT(*) 
            FROM (
                SELECT pr.id
                FROM purch_req pr
                LEFT JOIN purch_req_doc prd ON prd.purch_req_id = pr.id
                WHERE pr.job_id = :jobId
                GROUP BY pr.id
                HAVING COUNT(prd.id) > 0
            ) t
        ")
                ->bindValue(':jobId', $id)
                ->queryScalar();

            // ถ้าครบทุกใบ
            if ($complete == $total) {
                return 100;
            }

            // มีใบขอซื้อ แต่ยังไม่ครบทุกใบ
            return 1;
        });
    }




    /**
     * ตรวจสอบว่ามีใบสั่งซื้อหรือไม่
     * @return bool
     */
//    public function hasPurchaseOrder($id)
//    {
//        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM purch p INNER JOIN purch_doc pd ON pd.purch_id = p.id WHERE p.job_id = :jobId')
//            ->bindParam(':jobId', $id)
//            ->queryScalar();
//        return $count > 0;
//    }

    public function hasPurchaseOrder($jobId)
    {
        $db = Yii::$app->db;

        // นับใบสั่งซื้อทั้งหมด
        $total = $db->createCommand("
        SELECT COUNT(*) 
        FROM purch 
        WHERE job_id = :jobId
    ")
            ->bindValue(':jobId', $jobId)
            ->queryScalar();

        // ถ้าไม่มีใบสั่งซื้อเลย
        if ($total == 0) {
            return 0;
        }

        // นับใบสั่งซื้อที่มีไฟล์แนบ
        $complete = $db->createCommand("
        SELECT COUNT(*) 
        FROM (
            SELECT p.id, COUNT(pd.id) AS doc_count
            FROM purch p
            LEFT JOIN purch_doc pd ON pd.purch_id = p.id
            WHERE p.job_id = :jobId
            GROUP BY p.id
        ) AS t
        WHERE t.doc_count > 0
    ")
            ->bindValue(':jobId', $jobId)
            ->queryScalar();

        // ✔ ทุกใบมีไฟล์แนบครบ
        if ($complete == $total) {
            return 100;
        }

        // ❗ มีใบสั่งซื้อแต่ไฟล์แนบยังไม่ครบ
        return 1;
    }



    /**
     * ตรวจสอบว่ามีรายการรับสินค้าหรือไม่
     * @return bool
     */
    public function hasReceiveTransaction($jobId)
    {
        $db = Yii::$app->db;

        // นับรายการรับสินค้าทั้งหมด
        $total = $db->createCommand("
        SELECT COUNT(*) 
        FROM journal_trans jt
        LEFT JOIN purch p ON p.id = jt.trans_ref_id
        WHERE jt.trans_type_id = 1 AND jt.job_id = :jobId
    ")
            ->bindValue(':jobId', $jobId)
            ->queryScalar();

        if ($total == 0) {
            return 0;
        }

        // นับรายการที่มีไฟล์แนบใน purch_receive_doc
        $complete = $db->createCommand("
        SELECT COUNT(*) 
        FROM (
            SELECT jt.id, COUNT(prd.id) AS doc_count
            FROM journal_trans jt
            LEFT JOIN purch p ON p.id = jt.trans_ref_id
            LEFT JOIN purch_receive_doc prd ON prd.purch_id = p.id
            WHERE jt.trans_type_id = 1 AND jt.job_id = :jobId
            GROUP BY jt.id
        ) AS t
        WHERE t.doc_count > 0
    ")
            ->bindValue(':jobId', $jobId)
            ->queryScalar();

        if ($complete == $total) {
            return 100;
        }

        return 1; // มีรายการแต่ไฟล์ยังไม่ครบ
    }


    /**
     * ตรวจสอบว่ามีรายการเบิกสินค้าหรือไม่
     * @return bool
     */
    public function hasWithdrawTransaction($id)
    {
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM journal_trans WHERE job_id = :jobId AND trans_type_id in(3,5)')
            ->bindParam(':jobId', $id)
            ->queryScalar();
        return $count > 0;
    }

    /**
     * ตรวจสอบว่ามีการแจ้งหนี้หรือไม่
     * @return bool
     */
    public function hasDebtNotification($id)
    {
        // สมมติว่ามีตาราง debt_notification หรือใช้จาก invoices ที่เป็น draft
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM invoices WHERE job_id = :jobId AND status = 1')
            ->bindParam(':jobId', $id)
            ->queryScalar();
        return $count > 0;
    }

    /**
     * ตรวจสอบว่ามีการวางบิลหรือไม่
     * @return bool
     */
    public function hasBilling($id)
    {
        // สมมติว่าใช้จาก invoices ที่มีสถานะ pending หรือ sent
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM invoices i LEFT JOIN billing_invoice_items b ON b.invoice_id = i.id LEFT JOIN quotation q ON q.id =i.quotation_id LEFT JOIN job j ON j.quotation_id = q.id LEFT JOIN invoice_doc ivd ON ivd.invoice_id = i.id  WHERE j.id = :jobId AND i.is_billed=1')
            ->bindParam(':jobId', $id)
            ->queryScalar();
        return $count > 0;
    }

    /**
     * ตรวจสอบว่ามีใบกำกับภาษีหรือไม่
     * @return bool
     */
    public function hasTaxInvoice($id)
    {
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM invoices i LEFT JOIN quotation q ON q.id =i.quotation_id LEFT JOIN job j ON j.quotation_id = q.id LEFT JOIN invoice_doc ivd ON ivd.invoice_id = i.id WHERE j.id = :jobId AND i.invoice_type = \'tax_invoice\'')
            ->bindParam(':jobId', $id)
            ->queryScalar();
        return $count > 0;
    }

    /**
     * ตรวจสอบว่ามีใบเสร็จหรือไม่
     * @return bool
     */
    public function hasReceipt($id)
    {
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM invoices i LEFT JOIN quotation q ON q.id =i.quotation_id LEFT JOIN job j ON j.quotation_id = q.id LEFT JOIN invoice_doc ivd ON ivd.invoice_id = i.id WHERE j.id = :jobId AND i.invoice_type = \'receipt\'')
            ->bindParam(':jobId', $id)
            ->queryScalar();
        return $count > 0;
    }

    public function hasPayment($id)
    {
        $count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM payment_receipts WHERE job_id=:jobId')
            ->bindParam(':jobId', $id)->queryScalar();
        return $count > 0;
    }

    /**
     * ตรวจสอบว่ามีเงินสดย่อยหรือไม่
     * @param integer $jobId
     * @return boolean
     */
    public function hasPettyCash($jobId)
    {
        $sql = "SELECT COUNT(*) FROM petty_cash_voucher pcv LEFT JOIN petty_cash_voucher_doc_bill pcvd ON pcvd.petty_cash_voucher_id = pcv.id WHERE job_id = :jobId AND approve_status = 1";
        $count = Yii::$app->db->createCommand($sql)
            ->bindParam(':jobId', $jobId)
            ->queryScalar();

        return $count > 0;
    }

    public function getJobExpenses()
    {
        return $this->hasMany(\backend\models\JobExpense::className(), ['job_id' => 'id']);
    }

    public function getCompany()
    {
        return $this->hasOne(\backend\models\Company::className(), ['id' => 'company_id']);
    }

    public function getJobexpenseAll()
    {
        $total = 0;
        foreach ($this->jobExpenses as $value) {
            $total = $total + ($value->line_amount);
        }
        return $total;
    }

    public function getVehicleExpenseAll()
    {
        return \backend\models\VehicleExpense::find()->where(['job_no' => $this->job_no])->sum('total_wage');
    }

    public function beforeSave($insert)
    {
        $this->company_id = \Yii::$app->session->get('company_id');
        return true;
    }

    private function cacheActivity($key, $callback)
    {
        if (!array_key_exists($key, $this->_activityCache)) {
            $this->_activityCache[$key] = call_user_func($callback);
        }
        return $this->_activityCache[$key];
    }

}
