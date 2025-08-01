<?php
namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

class DebitNoteItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'debit_note_item';
    }

    public function rules()
    {
        return [
            [['debit_note_id', 'item_no', 'description'], 'required'],
            [['debit_note_id', 'item_no'], 'integer'],
            [['description'], 'string'],
            [['quantity', 'unit_price', 'amount'], 'number'],
            [['unit'], 'string', 'max' => 50],
            [['quantity'], 'default', 'value' => 1],
            [['unit_price', 'amount'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'debit_note_id' => 'Debit Note ID',
            'item_no' => 'ลำดับ',
            'description' => 'รายการ',
            'quantity' => 'จำนวน',
            'unit' => 'หน่วย',
            'unit_price' => 'ราคาต่อหน่วย',
            'amount' => 'จำนวนเงิน',
        ];
    }

    public static function createMultiple($modelClass, $multipleModels = [])
    {
        $model = new $modelClass;
        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (!empty($post)) {
            foreach ($post as $i => $item) {
                if ($i == 0) {
                    $models[] = $model;
                } else {
                    $models[] = new $modelClass;
                }
            }
        }

        return $models;
    }
}