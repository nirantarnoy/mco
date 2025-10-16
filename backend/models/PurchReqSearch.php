<?php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PurchReqSearch represents the model behind the search form of `backend\models\PurchReq`.
 */
class PurchReqSearch extends PurchReq
{
    public $vendor_name;
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
        $query = PurchReq::find()->alias('pr')->joinWith(['vendor v']);

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
            'pr.id' => $this->id,
            'pr.purch_req_date' => $this->purch_req_date,
            'pr.vendor_id' => $this->vendor_id,
            'pr.status' => $this->status,
            'pr.approve_status' => $this->approve_status,
            'pr.total_amount' => $this->total_amount,
            'pr.discount_amount' => $this->discount_amount,
            'pr.vat_amount' => $this->vat_amount,
            'pr.net_amount' => $this->net_amount,
            'pr.created_at' => $this->created_at,
            'pr.created_by' => $this->created_by,
            'pr.updated_at' => $this->updated_at,
            'pr.updated_by' => $this->updated_by,
        ]);

        // Handle purch_id filter for converted status
        if (isset($params['PurchReqSearch']['purch_id'])) {
            if ($params['PurchReqSearch']['purch_id'] == '1') {
                $query->andWhere(['!=', 'purch_id', null]);
            } elseif ($params['PurchReqSearch']['purch_id'] == '0') {
                $query->andWhere(['purch_id' => null]);
            }
        }

        $query->andFilterWhere(['like', 'pr.purch_req_no', $this->purch_req_no])
            ->andFilterWhere(['like', 'v.name', $this->vendor_name])
            ->andFilterWhere(['like', 'pr.note', $this->note])
            ->andFilterWhere(['like', 'pr.total_text', $this->total_text]);

        return $dataProvider;
    }
}