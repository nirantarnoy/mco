<?php
namespace backend\models;

use Yii;

class CreditNoteItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'credit_note_item';
    }

    public function rules()
    {
        return [
          //  [['credit_note_id', 'product_id', 'description'], 'required'],
            [['credit_note_id', 'item_no','product_id','unit_id'], 'integer'],
            [['description'], 'string'],
            [['quantity', 'unit_price', 'amount', 'discount_amount'], 'number'],
            [['unit'], 'string', 'max' => 50],
            [['quantity'], 'default', 'value' => 1],
            [['unit_price', 'amount', 'discount_amount'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'credit_note_id' => 'Credit Note ID',
            'item_no' => 'ลำดับ',
            'description' => 'รายการ',
            'quantity' => 'จำนวน',
            'unit' => 'หน่วย',
            'unit_price' => 'ราคาต่อหน่วย',
            'amount' => 'จำนวนเงิน',
            'discount_amount' => 'ส่วนลด',
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