<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "document_revision".
 *
 * @property int $id
 * @property string $doc_type PO, NPR
 * @property int $doc_id
 * @property string $doc_no
 * @property int $rev
 * @property string|null $data_json
 * @property string|null $note
 * @property int|null $created_at
 * @property int|null $created_by
 */
class DocumentRevision extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_revision';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
            [
                'class' => BlameableBehavior::className(),
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
            [['doc_type', 'doc_id', 'doc_no'], 'required'],
            [['doc_id', 'rev', 'created_at', 'created_by'], 'integer'],
            [['data_json', 'note'], 'string'],
            [['doc_type'], 'string', 'max' => 20],
            [['doc_no'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'doc_type' => 'ประเภทเอกสาร',
            'doc_id' => 'Doc ID',
            'doc_no' => 'เลขที่เอกสาร',
            'rev' => 'ครั้งที่แก้ไข',
            'data_json' => 'Data Json',
            'note' => 'หมายเหตุ (ถ้ามี)',
            'created_at' => 'วันที่เก็บประวัติ',
            'created_by' => 'ผู้เก็บประวัติ',
        ];
    }

    /**
     * Create revision snapshot for Purch
     */
    public static function createPurchRevision($purch)
    {
        $revision = new self();
        $revision->doc_type = 'PO';
        $revision->doc_id = $purch->id;
        $revision->doc_no = $purch->purch_no;
        $revision->rev = $purch->rev;
        
        $data = [
            'master' => $purch->attributes,
            'lines' => []
        ];
        
        $lines = \backend\models\PurchLine::find()->where(['purch_id' => $purch->id])->all();
        foreach ($lines as $line) {
            $data['lines'][] = $line->attributes;
        }
        
        $revision->data_json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $revision->save(false);
    }

    /**
     * Create revision snapshot for None PR (PurchaseMaster)
     */
    public static function createNprRevision($npr, $rev)
    {
        $revision = new self();
        $revision->doc_type = 'NPR';
        $revision->doc_id = $npr->id;
        $revision->doc_no = $npr->docnum;
        $revision->rev = $rev;
        
        $data = [
            'master' => $npr->attributes,
            'lines' => []
        ];
        
        $lines = \backend\models\PurchaseDetail::find()->where(['purchase_master_id' => $npr->id])->orderBy(['line_no' => SORT_ASC])->all();
        foreach ($lines as $line) {
            $data['lines'][] = $line->attributes;
        }
        
        $revision->data_json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $revision->save(false);
    }
}
