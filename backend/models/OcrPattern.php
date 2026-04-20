<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "ocr_pattern".
 *
 * @property int $id
 * @property string $name
 * @property string $tax_id
 * @property string $regex_invoice_no
 * @property string $regex_date
 * @property string $regex_total
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 */
class OcrPattern extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ocr_pattern}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'tax_id'], 'required'],
            [['status', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['name', 'parsing_strategy'], 'string', 'max' => 255],
            [['tax_id'], 'string', 'max' => 20],
            [['regex_invoice_no', 'regex_date', 'regex_total', 'regex_item_start'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อรูปแบบ (เช่น ชื่อลูกค้า/ผู้ขาย)',
            'tax_id' => 'เลขประจำตัวผู้เสียภาษี',
            'regex_invoice_no' => 'Regex สำหรับเลขที่เอกสาร',
            'regex_date' => 'Regex สำหรับวันที่',
            'regex_total' => 'Regex สำหรับยอดเงินรวม',
            'regex_item_start' => 'Regex จุดเริ่มต้นบรรทัดสินค้า',
            'parsing_strategy' => 'กลยุทธ์การอ่าน (block/collector)',
            'status' => 'สถานะ',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
        ];
    }
}
