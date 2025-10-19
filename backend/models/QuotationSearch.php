<?php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * QuotationSearch represents the model behind the search form of `backend\models\Quotation`.
 */
class QuotationSearch extends Quotation
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'status', 'approve_status', 'approve_by', 'created_by', 'updated_by'], 'integer'],
            [['quotation_no', 'quotation_date', 'customer_name', 'total_amount_text', 'note'], 'safe'],
            [['total_amount'], 'number'],
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
        $query = Quotation::find();

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
            'quotation_date' => $this->quotation_date,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'approve_status' => $this->approve_status,
            'approve_by' => $this->approve_by,
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['company_id'=> \Yii::$app->session->get('company_id')]);

        $query->andFilterWhere(['like', 'quotation_no', $this->quotation_no])
            ->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'total_amount_text', $this->total_amount_text])
            ->andFilterWhere(['like', 'note', $this->note]);

        return $dataProvider;
    }
}