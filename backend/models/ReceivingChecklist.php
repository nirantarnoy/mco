<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "receiving_checklist".
 *
 * @property int $id
 * @property int $purch_id
 * @property int|null $journal_trans_id
 * @property string $check_date
 * @property string|null $checker_name
 * @property string|null $general_condition
 * @property int|null $correct_items
 * @property int|null $correct_quantity
 * @property int|null $correct_spec
 * @property int|null $has_certificate
 * @property int|null $has_manual
 * @property string|null $notes
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Purch $purch
 * @property JournalTrans $journalTrans
 */
class ReceivingChecklist extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'receiving_checklist';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_id', 'check_date'], 'required'],
            [['purch_id', 'journal_trans_id', 'correct_items', 'correct_quantity', 'correct_spec', 'has_certificate', 'has_manual', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['check_date'], 'safe'],
            [['general_condition', 'notes'], 'string'],
            [['checker_name'], 'string', 'max' => 100],
            [['purch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Purch::class, 'targetAttribute' => ['purch_id' => 'id']],
            [['journal_trans_id'], 'exist', 'skipOnError' => true, 'targetClass' => JournalTrans::class, 'targetAttribute' => ['journal_trans_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_id' => 'เลขที่ใบสั่งซื้อ',
            'journal_trans_id' => 'เลขที่การรับสินค้า',
            'check_date' => 'วันที่ตรวจสอบ',
            'checker_name' => 'ชื่อผู้ตรวจสอบ',
            'general_condition' => 'สภาพทั่วไปของสินค้า',
            'correct_items' => 'สินค้าตรงตาม',
            'correct_quantity' => 'จำนวนและขนาด',
            'correct_spec' => 'จำนวนที่สั่ง',
            'has_certificate' => 'ใบ Certificate',
            'has_manual' => 'คู่มือการใช้งาน',
            'notes' => 'หมายเหตุ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
            'created_by' => 'สร้างโดย',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * Gets query for [[Purch]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurch()
    {
        return $this->hasOne(Purch::class, ['id' => 'purch_id']);
    }

    /**
     * Gets query for [[JournalTrans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTrans()
    {
        return $this->hasOne(JournalTrans::class, ['id' => 'journal_trans_id']);
    }

    /**
     * Get general condition as array
     * @return array
     */
    public function getGeneralConditionArray()
    {
        if (empty($this->general_condition)) {
            return array_fill(0, 16, 0); // 0-15 indices
        }
        $data = json_decode($this->general_condition, true);
        return is_array($data) ? $data : array_fill(0, 16, 0);
    }

    /**
     * Set general condition from array
     * @param array $data
     */
    public function setGeneralConditionArray($data)
    {
        $this->general_condition = json_encode($data);
    }

    /**
     * Get checklist summary
     * @return array
     */
    public function getChecklistSummary()
    {
        $generalCondition = $this->getGeneralConditionArray();
        $generalChecked = array_sum(array_filter($generalCondition));

        return [
            'general_total' => 15,
            'general_checked' => $generalChecked,
            'correct_items' => $this->correct_items ? 'ครบถ้วน' : 'ไม่ครบถ้วน',
            'correct_quantity' => $this->correct_quantity ? 'ถูกต้อง' : 'ไม่ถูกต้อง',
            'correct_spec' => $this->correct_spec ? 'ถูกต้อง' : 'ไม่ถูกต้อง',
            'has_certificate' => $this->has_certificate ? 'มี' : 'ไม่มี',
            'has_manual' => $this->has_manual ? 'มี' : 'ไม่มี',
        ];
    }

    /**
     * Check if checklist is complete
     * @return bool
     */
    public function isComplete()
    {
        return $this->correct_items && $this->correct_quantity && $this->correct_spec;
    }
}
