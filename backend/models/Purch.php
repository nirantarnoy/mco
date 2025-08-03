<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "purch".
 *
 * @property int $id
 * @property string|null $purch_no
 * @property string|null $purch_date
 * @property int|null $vendor_id
 * @property string|null $vendor_name
 * @property int|null $status
 * @property string|null $note
 * @property int|null $approve_status
 * @property float|null $total_amount
 * @property float|null $discount_amount
 * @property float|null $vat_amount
 * @property float|null $net_amount
 * @property string|null $ref_text
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property PurchLine[] $purchLines
 * @property PurchReq[] $purchReqs
 */
class Purch extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 2;
    const STATUS_COMPLETED = 3;

    const APPROVE_STATUS_PENDING = 0;
    const APPROVE_STATUS_APPROVED = 1;
    const APPROVE_STATUS_REJECTED = 2;

    /**
     * @var PurchLine[] $purchLines for form handling
     */
    public $purchLines = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch';
    }

    /**
     * {@inheritdoc}
     */
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
            'timestampcby'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_by',
                ],
                'value'=> Yii::$app->user->id,
            ],
            'timestamuby'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_UPDATE=>'updated_by',
                ],
                'value'=> Yii::$app->user->id,
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id'], 'required'],
            [['purch_date'], 'safe'],
            [['vendor_id', 'status', 'approve_status', 'created_at', 'created_by', 'updated_at', 'updated_by','discount_percent','vat_percent'], 'integer'],
            [['total_amount', 'discount_amount', 'vat_amount', 'net_amount'], 'number'],
            [['purch_no', 'vendor_name', 'note','delivery_note','payment_note','footer_delivery','footer_payment','ref_no'], 'string', 'max' => 255],
            [['purch_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_no' => 'เลขที่ใบสั่งซื้อ',
            'purch_date' => 'วันที่',
            'vendor_id' => 'รหัสผู้ขาย',
            'vendor_name' => 'ชื่อผู้ขาย',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
            'approve_status' => 'สถานะอนุมัติ',
            'total_amount' => 'ยอดรวม',
            'discount_amount' => 'ส่วนลด',
            'vat_amount' => 'VAT',
            'net_amount' => 'ยอดรวมสุทธิ',
           // 'ref_text' => 'อ้างอิง',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'วันที่แก้ไข',
            'updated_by' => 'แก้ไขโดย',
            'delivery_note' => 'หมายเหตุการจัดส่ง',
            'payment_note' => 'หมายเหตุการชําระเงิน',
            'ref_no' => 'REF NO',
        ];
    }

    /**
     * Gets query for [[PurchLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchLines()
    {
        return $this->hasMany(PurchLine::class, ['purch_id' => 'id']);
    }

    /**
     * Gets query for [[PurchReqs]] - Purchase requests that reference this PO
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchReqs()
    {
        return $this->hasMany(PurchReq::class, ['purch_id' => 'id']);
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        $statuses = [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
        return $statuses[$this->status] ?? 'ไม่ระบุ';
    }

    /**
     * Get approve status label
     */
    public function getApproveStatusLabel()
    {
        $statuses = [
            self::APPROVE_STATUS_PENDING => 'รอพิจารณา',
            self::APPROVE_STATUS_APPROVED => 'อนุมัติ',
            self::APPROVE_STATUS_REJECTED => 'ไม่อนุมัติ',
        ];
        return $statuses[$this->approve_status] ?? 'ไม่ระบุ';
    }

    /**
     * Get approve status badge
     */
    public function getApproveStatusBadge()
    {
        $badges = [
            self::APPROVE_STATUS_PENDING => '<span class="badge badge-warning">รอพิจารณา</span>',
            self::APPROVE_STATUS_APPROVED => '<span class="badge badge-success">อนุมัติ</span>',
            self::APPROVE_STATUS_REJECTED => '<span class="badge badge-danger">ไม่อนุมัติ</span>',
        ];
        return $badges[$this->approve_status] ?? '<span class="badge badge-secondary">ไม่ระบุ</span>';
    }

    /**
     * Calculate total amount from purch lines
     */
    public function calculateTotalAmount()
    {
        $total = 0;
        foreach ($this->purchLines as $line) {
            $total += $line->line_total;
        }
        return $total;
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->purch_no)) {
                $this->purch_no = $this->generatePurchNo();
            }
            return true;
        }
        return false;
    }

    /**
     * Generate purchase number
     */
    private function generatePurchNo()
    {
        $prefix = 'PO' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'purch_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->purch_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    /**
     * Get related purchase requests
     */
    public function getRelatedPurchReqs()
    {
        return $this->getPurchReqs()->all();
    }
    public static function checkPoremain($purchId)
    {
        $sql = "
            SELECT 
                pl.*,
                COALESCE(received.total_received, 0) as total_received,
                (pl.qty - COALESCE(received.total_received, 0)) as remaining_qty
            FROM purch_line pl
            LEFT JOIN (
                SELECT 
                    product_id,
                    SUM(jtl.qty) as total_received
                FROM journal_trans_line jtl
                INNER JOIN journal_trans jt ON jtl.journal_trans_id = jt.id
                WHERE jt.trans_ref_id = :purchId 
                AND jt.trans_type_id = :transType 
                AND jt.po_rec_status = :status
                GROUP BY product_id
            ) received ON pl.product_id = received.product_id
            WHERE pl.purch_id = :purchId 
            AND pl.status = :lineStatus
            AND (pl.qty - COALESCE(received.total_received, 0)) > 0
        ";

        return \Yii::$app->db->createCommand($sql, [
            ':purchId' => $purchId,
            ':transType' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE,
            ':status' => 1,
            ':lineStatus' => \backend\models\PurchLine::STATUS_ACTIVE,
        ])->queryAll();
    }

    public static function findVendorName($vendor_id){
        $name = '';
        $model = \backend\models\Vendor::findOne($vendor_id);
        if($model){
            $name = $model->name;
        }
        return $name;
    }
    public static function findVendorTaxID($vendor_id){
        $name = '';
        $model = \backend\models\Vendor::findOne($vendor_id);
        if($model){
            $name = $model->taxid;
        }
        return $name;
    }
    public static function findVendorAddress($vendor_id){
//        $address = '';
//        $model_address = \backend\models\AddressInfo::find()->where(['party_id'=>$vendor_id,'party_type_id'=>2])->one();
//        if($model_address){
//            $address = $model_address->address.' '.$model_address->street;
//            $model_district = \common\models\District::find()->where(['DISTRICT_ID'=>$model_address->district_id])->one();
//            if($model_district){
//                $address = $address.' '.$model_district->DISTRICT_NAME;
//            }
//            $model_city = \common\models\Amphur::find()->where(['AMPHUR_ID'=>$model_address->city_id])->one();
//            if($model_city){
//                $address = $address.' '.$model_city->CITY_NAME;
//            }
//            $model_province = \common\models\Province::find()->where(['PROVINCE_ID'=>$model_address->province_id])->one();
//            if($model_province){
//                $address = $address.' '.$model_province->PROVINCE_NAME;
//            }
//            $address = $address.' '.$model_address->zipcode;
//
//        }
//        return $address;

        $address = '';
        $model = \backend\models\Vendor::findOne($vendor_id);
        if($model){
            $address = $model->home_number.' '.$model->street.' '.$model->district_name.' '.$model->city_name.' '.$model->province_name.' '.$model->zipcode;
        }
        return $address;
    }

    public static function findNo($id){
        $no = '';
        $model = \backend\models\Purch::findOne($id);
        if($model){
            $no = $model->purch_no;
        }
        return $no;
    }

    public function getVendor(){
        return $this->hasOne(\backend\models\Vendor::className(), ['id' => 'vendor_id']);
    }
}