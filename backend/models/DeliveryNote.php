<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "delivery_note".
 *
 * @property int $id
 * @property string|null $dn_no
 * @property string|null $date
 * @property int|null $job_id
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property string|null $address
 * @property string|null $attn
 * @property string|null $our_ref
 * @property string|null $from_name
 * @property string|null $tel
 * @property string|null $ref_no
 * @property string|null $page_no
 * @property int|null $status
 * @property int|null $company_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property DeliveryNoteLine[] $deliveryNoteLines
 * @property Job $job
 */
class DeliveryNote extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELLED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'delivery_note';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'job_id'], 'required'],
            [['date'], 'safe'],
            [['job_id', 'customer_id', 'status', 'company_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['address'], 'string'],
            [['dn_no', 'customer_name', 'attn', 'our_ref', 'from_name', 'tel', 'ref_no', 'page_no'], 'string', 'max' => 255],
            [['dn_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dn_no' => 'เลขที่ใบส่งของ',
            'date' => 'วันที่',
            'job_id' => 'Job No.',
            'customer_id' => 'ลูกค้า',
            'customer_name' => 'ชื่อลูกค้า',
            'address' => 'ที่อยู่',
            'attn' => 'Attn',
            'our_ref' => 'Our Ref',
            'from_name' => 'From',
            'tel' => 'Tel',
            'ref_no' => 'Ref No.',
            'page_no' => 'Page No.',
            'status' => 'สถานะ',
            'company_id' => 'Company ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[DeliveryNoteLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryNoteLines()
    {
        return $this->hasMany(DeliveryNoteLine::class, ['delivery_note_id' => 'id']);
    }

    /**
     * Gets query for [[Job]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->dn_no)) {
                $this->dn_no = $this->generateDnNo();
            }
            $this->company_id = \Yii::$app->session->get('company_id');
            return true;
        }
        return false;
    }

    public function generateDnNo()
    {
        $date = $this->date ? strtotime($this->date) : time();
        $prefix = 'DN' . date('y', $date) . str_pad(date('m', $date), 2, '0', STR_PAD_LEFT);
        $lastRecord = self::find()
            ->where(['like', 'dn_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->dn_no, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%03d', $newNumber);
    }
}
