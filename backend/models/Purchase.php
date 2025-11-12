<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "purchase".
 *
 * @property int $id
 * @property string|null $dedcod รหัสแผนก
 * @property string|null $docnum เลขที่เอกสาร
 * @property string|null $docdat วันที่เอกสาร
 * @property string|null $supcod รหัสผู้จำหน่าย
 * @property string|null $supnam ชื่อผู้จำหน่าย
 * @property string|null $stkcod รหัสสินค้า
 * @property string|null $stkdes รายละเอียดสินค้า
 * @property float|null $trnqty จำนวน
 * @property string|null $untpri ราคาต่อหน่วย
 * @property float|null $disc ส่วนลดต่อหน่วย จำนวนเงิน
 * @property float|null $amount จำนวนเงิน
 * @property string|null $payfrm วิธีชำระ
 * @property string|null $duedat วันครบกำหนด
 * @property string|null $taxid เลขประจำตัวผู้เสียภาษี
 * @property string|null $discod ส่วนลดทั่วไป
 * @property float|null $addr01 ที่อยู่บรรทัด 1
 * @property float|null $addr02 ที่อยู่บรรทัด 2
 * @property float|null $addr03 ที่อยู่บรรทัด 3
 * @property string|null $zipcod รหัสไปรษณีย์
 * @property string|null $telnum เบอร์โทร
 * @property string|null $orgnum ลำดับเรียง
 * @property string|null $refnum เลขที่ใบกำกับ
 * @property string|null $vatdat ภาษี
 * @property float|null $vatpr0 มูลค่าภาษีในระดับ
 * @property string|null $late ยังไม่ได้เอกสาร
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Purchase extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purchase';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dedcod','docnum','docdat','supcod','supnam','stkcod','taxid'],'required'],
            [['docdat', 'duedat', 'vatdat', 'created_at', 'updated_at'], 'safe'],
            [['trnqty', 'disc', 'amount', 'vatpr0'], 'number'],
            [['dedcod','supcod','stkcod','payfrm','orgnum','created_by', 'updated_by'], 'integer'],
            [[ 'docnum',  'supnam',  'stkdes', 'untpri',  'taxid', 'discod', 'zipcod', 'telnum',  'refnum', 'late','addr01', 'addr02', 'addr03'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dedcod' => 'รหัสแผนก',
            'docnum' => 'เลขที่เอกสาร',
            'docdat' => 'วันที่เอกสาร',
            'supcod' => 'รหัสผู้จำหน่าย',
            'supnam' => 'ชื่อผู้จำหน่าย',
            'stkcod' => 'รหัสสินค้า',
            'stkdes' => 'รายละเอียดสินค้า',
            'trnqty' => 'จำนวน',
            'untpri' => 'ราคาต่อหน่วย',
            'disc' => 'ส่วนลด',
            'amount' => 'จำนวนเงิน',
            'payfrm' => 'วิธีชำระ',
            'duedat' => 'วันครบกำหนด',
            'taxid' => 'เลขประจำตัวผู้เสียภาษี',
            'discod' => 'ส่วนลดทั่วไป',
            'addr01' => 'ที่อยู่ 1',
            'addr02' => 'ที่อยู่ 2',
            'addr03' => 'ที่อยู่ 3',
            'zipcod' => 'รหัสไปรษณีย์',
            'telnum' => 'เบอร์โทร',
            'orgnum' => 'ลำดับเรียง',
            'refnum' => 'เลขที่ใบกำกับ',
            'vatdat' => 'วันที่ภาษี',
            'vatpr0' => 'มูลค่าภาษี',
            'late' => 'ยังไม่ได้เอกสาร',
            'created_at' => 'สร้างเมื่อ',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'แก้ไขเมื่อ',
            'updated_by' => 'แก้ไขโดย',
        ];
    }
}