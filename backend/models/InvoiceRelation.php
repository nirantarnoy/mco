<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "invoice_relations".
 *
 * @property int $id
 * @property int $parent_invoice_id
 * @property int $child_invoice_id
 * @property string $relation_type
 * @property string $created_at
 *
 * @property Invoice $parentInvoice
 * @property Invoice $childInvoice
 */
class InvoiceRelation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_relations';
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
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_invoice_id', 'child_invoice_id', 'relation_type'], 'required'],
            [['parent_invoice_id', 'child_invoice_id'], 'integer'],
            [['relation_type'], 'string', 'max' => 50],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_invoice_id' => 'เอกสารต้นฉบับ',
            'child_invoice_id' => 'เอกสารที่ Copy',
            'relation_type' => 'ประเภทความสัมพันธ์',
            'created_at' => 'วันที่สร้าง',
        ];
    }

    /**
     * Gets query for [[ParentInvoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParentInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'parent_invoice_id']);
    }

    /**
     * Gets query for [[CreditNote]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreditNote()
    {
        return $this->hasOne(CreditNote::class, ['id' => 'parent_invoice_id']);
    }

    /**
     * Gets query for [[DebitNote]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDebitNote()
    {
        return $this->hasOne(DebitNote::class, ['id' => 'parent_invoice_id']);
    }

    /**
     * Gets query for [[ChildInvoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChildInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'child_invoice_id']);
    }

    /**
     * ตรวจสอบและยกเลิก Foreign Key fk_invoice_relations_parent เพื่อให้สามารถเก็บ ID ของ CreditNote และ DebitNote ได้
     */
    public static function ensureNoParentFk()
    {
        try {
            $db = Yii::$app->db;
            $table = $db->getTableSchema(self::tableName(), true);
            if ($table && isset($table->foreignKeys['fk_invoice_relations_parent'])) {
                $db->createCommand("ALTER TABLE `" . self::tableName() . "` DROP FOREIGN KEY `fk_invoice_relations_parent`")->execute();
                $db->getSchema()->refreshTableSchema(self::tableName());
            }
        } catch (\Exception $e) {
            // Ignored if already dropped
        }
    }
}
