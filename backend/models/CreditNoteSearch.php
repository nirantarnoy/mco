<?php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class CreditNoteSearch extends CreditNote
{
    public function rules()
    {
        return [
            [['id', 'customer_id', 'invoice_id'], 'integer'],
            [['document_no', 'document_date', 'status', 'reason'], 'safe'],
            [['adjust_amount', 'vat_amount', 'total_amount'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = CreditNote::find();
        $query->with(['customer']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'document_date' => SORT_DESC,
                    'document_no' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'invoice_id' => $this->invoice_id,
            'adjust_amount' => $this->adjust_amount,
            'vat_amount' => $this->vat_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'document_no', $this->document_no])
            ->andFilterWhere(['like', 'reason', $this->reason]);

        // Date range filter
        if (!empty($this->document_date) && strpos($this->document_date, ' - ') !== false) {
            list($start_date, $end_date) = explode(' - ', $this->document_date);
            $query->andFilterWhere(['between', 'document_date', $start_date, $end_date]);
        }

        return $dataProvider;
    }
}