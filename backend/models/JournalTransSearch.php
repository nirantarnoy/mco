<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\JournalTrans;

/**
 * JournalTransSearch represents the model behind the search form of `common\models\JournalTransX`.
 */
class JournalTransSearch extends JournalTrans
{
    public $globalSearch;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'trans_type_id', 'stock_type_id', 'customer_id', 'qty', 'created_at', 'created_by', 'updated_at', 'updated_by', 'party_id', 'party_type_id', 'warehouse_id', 'trans_ref_id'], 'integer'],
            [['trans_date', 'journal_no', 'customer_name', 'remark', 'status','globalSearch'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = JournalTrans::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'trans_date' => $this->trans_date,
            'trans_type_id' => $this->trans_type_id,
            'stock_type_id' => $this->stock_type_id,
            'customer_id' => $this->customer_id,
            'qty' => $this->qty,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'party_id' => $this->party_id,
            'party_type_id' => $this->party_type_id,
            'warehouse_id' => $this->warehouse_id,
            'trans_ref_id' => $this->trans_ref_id,
            'status' => $this->status,
        ]);

        $company_id = \Yii::$app->session->get('company_id');
        if ($company_id != 1) {
            $query->andFilterWhere(['company_id' => $company_id]);
        }

        if($this->globalSearch !=''){
            $query->andFilterWhere(['like', 'journal_no', $this->globalSearch])
                ->orFilterWhere(['like', 'customer_name', $this->globalSearch])
                ->orFilterWhere(['like', 'remark', $this->globalSearch]);
        }


        return $dataProvider;
    }
}