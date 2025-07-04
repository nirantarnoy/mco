<?php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PurchReqSearch represents the model behind the search form of `backend\models\PurchReq`.
 */
class PurchReqSearch extends PurchReq
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'vendor_id', 'status', 'approve_status', 'purch_id', 'created_by', 'updated_by'], 'integer'],
            [['purch_req_no', 'purch_req_date', 'vendor_name', 'note', 'total_text'], 'safe'],
            [['total_amount', 'discount_amount', 'vat_amount', 'net_amount'], 'number'],
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
        $query = PurchReq::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
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
            'purch_req_date' => $this->purch_req_date,
            'vendor_id' => $this->vendor_id,
            'status' => $this->status,
            'approve_status' => $this->approve_status,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'vat_amount' => $this->vat_amount,
            'net_amount' => $this->net_amount,
            'purch_id' => $this->purch_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'purch_req_no', $this->purch_req_no])
            ->andFilterWhere(['like', 'vendor_name', $this->vendor_name])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'total_text', $this->total_text]);

        return $dataProvider;
    }
}