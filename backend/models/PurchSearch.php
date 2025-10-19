<?php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PurchSearch represents the model behind the search form of `backend\models\Purch`.
 */
class PurchSearch extends Purch
{
    public $vendor_name;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'vendor_id', 'status', 'approve_status', 'created_by', 'updated_by'], 'integer'],
            [['purch_no', 'purch_date', 'vendor_name', 'note',], 'safe'],
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
        $query = Purch::find()->alias('p')->joinWith(['vendor v']);

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
            'p.id' => $this->id,
            'p.purch_date' => $this->purch_date,
           // 'vendor_id' => $this->vendor_id,
            'p.status' => $this->status,
            'p.approve_status' => $this->approve_status,
            'p.total_amount' => $this->total_amount,
            'p.created_at' => $this->created_at,
            'p.created_by' => $this->created_by,
            'p.updated_at' => $this->updated_at,
            'p.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['company_id'=> \Yii::$app->session->get('company_id')]);

        $query->andFilterWhere(['like', 'p.purch_no', $this->purch_no])
            //->andFilterWhere(['like', 'vendor_name', $this->vendor_name])
            ->andFilterWhere(['like', 'v.name', $this->vendor_name])
            ->andFilterWhere(['like', 'p.note', $this->note]);

        return $dataProvider;
    }
}