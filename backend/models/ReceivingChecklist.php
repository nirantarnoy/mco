<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "receiving_checklist".
 * Version 2: Dynamic checklist based on number of items
 *
 * @property int $id
 * @property int $purch_id
 * @property int|null $journal_trans_id
 * @property string $check_date
 * @property string|null $checker_name
 * @property string|null $general_condition
 * @property string|null $correct_items
 * @property string|null $correct_quantity
 * @property string|null $correct_spec
 * @property string|null $has_certificate
 * @property string|null $has_manual
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
            [['purch_id', 'journal_trans_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['check_date'], 'safe'],
            [['general_condition', 'correct_items', 'correct_quantity', 'correct_spec', 'has_certificate', 'has_manual', 'notes'], 'string'],
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
            return [];
        }
        $data = json_decode($this->general_condition, true);
        return is_array($data) ? $data : [];
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
     * Get correct items as array
     * @return array
     */
    public function getCorrectItemsArray()
    {
        if (empty($this->correct_items)) {
            return [];
        }
        $data = json_decode($this->correct_items, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Set correct items from array
     * @param array $data
     */
    public function setCorrectItemsArray($data)
    {
        $this->correct_items = json_encode($data);
    }

    /**
     * Get correct quantity as array
     * @return array
     */
    public function getCorrectQuantityArray()
    {
        if (empty($this->correct_quantity)) {
            return [];
        }
        $data = json_decode($this->correct_quantity, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Set correct quantity from array
     * @param array $data
     */
    public function setCorrectQuantityArray($data)
    {
        $this->correct_quantity = json_encode($data);
    }

    /**
     * Get correct spec as array
     * @return array
     */
    public function getCorrectSpecArray()
    {
        if (empty($this->correct_spec)) {
            return [];
        }
        $data = json_decode($this->correct_spec, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Set correct spec from array
     * @param array $data
     */
    public function setCorrectSpecArray($data)
    {
        $this->correct_spec = json_encode($data);
    }

    /**
     * Get has certificate as array
     * @return array
     */
    public function getHasCertificateArray()
    {
        if (empty($this->has_certificate)) {
            return [];
        }
        $data = json_decode($this->has_certificate, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Set has certificate from array
     * @param array $data
     */
    public function setHasCertificateArray($data)
    {
        $this->has_certificate = json_encode($data);
    }

    /**
     * Get has manual as array
     * @return array
     */
    public function getHasManualArray()
    {
        if (empty($this->has_manual)) {
            return [];
        }
        $data = json_decode($this->has_manual, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Set has manual from array
     * @param array $data
     */
    public function setHasManualArray($data)
    {
        $this->has_manual = json_encode($data);
    }

    /**
     * Get checklist summary
     * @return array
     */
    public function getChecklistSummary()
    {
        $generalCondition = $this->getGeneralConditionArray();
        $correctItems = $this->getCorrectItemsArray();
        $correctQuantity = $this->getCorrectQuantityArray();
        $correctSpec = $this->getCorrectSpecArray();
        $hasCertificate = $this->getHasCertificateArray();
        $hasManual = $this->getHasManualArray();

        return [
            'general_checked' => count(array_filter($generalCondition)),
            'general_total' => count($generalCondition),
            'correct_items_checked' => count(array_filter($correctItems)),
            'correct_items_total' => count($correctItems),
            'correct_quantity_checked' => count(array_filter($correctQuantity)),
            'correct_quantity_total' => count($correctQuantity),
            'correct_spec_checked' => count(array_filter($correctSpec)),
            'correct_spec_total' => count($correctSpec),
            'has_certificate_checked' => count(array_filter($hasCertificate)),
            'has_certificate_total' => count($hasCertificate),
            'has_manual_checked' => count(array_filter($hasManual)),
            'has_manual_total' => count($hasManual),
        ];
    }

    /**
     * Check if checklist is complete (all items checked)
     * @return bool
     */
    public function isComplete()
    {
        $summary = $this->getChecklistSummary();

        return $summary['correct_items_checked'] == $summary['correct_items_total'] &&
            $summary['correct_quantity_checked'] == $summary['correct_quantity_total'] &&
            $summary['correct_spec_checked'] == $summary['correct_spec_total'];
    }

    /**
     * Get completion percentage
     * @return float
     */
    public function getCompletionPercentage()
    {
        $summary = $this->getChecklistSummary();

        $totalItems = $summary['general_total'] +
            $summary['correct_items_total'] +
            $summary['correct_quantity_total'] +
            $summary['correct_spec_total'] +
            $summary['has_certificate_total'] +
            $summary['has_manual_total'];

        $checkedItems = $summary['general_checked'] +
            $summary['correct_items_checked'] +
            $summary['correct_quantity_checked'] +
            $summary['correct_spec_checked'] +
            $summary['has_certificate_checked'] +
            $summary['has_manual_checked'];

        if ($totalItems == 0) {
            return 0;
        }

        return round(($checkedItems / $totalItems) * 100, 2);
    }
}
