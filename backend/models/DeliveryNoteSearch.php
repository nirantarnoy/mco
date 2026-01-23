<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\DeliveryNote;

/**
 * DeliveryNoteSearch represents the model behind the search form of `backend\models\DeliveryNote`.
 */
class DeliveryNoteSearch extends DeliveryNote
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'job_id', 'customer_id', 'status', 'company_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['dn_no', 'date', 'customer_name', 'address', 'attn', 'our_ref', 'from_name', 'tel', 'ref_no', 'page_no'], 'safe'],
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
        $query = DeliveryNote::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
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
            'date' => $this->date,
            'job_id' => $this->job_id,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'company_id' => $this->company_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['company_id' => \Yii::$app->session->get('company_id')]);

        $query->andFilterWhere(['like', 'dn_no', $this->dn_no])
            ->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'attn', $this->attn])
            ->andFilterWhere(['like', 'our_ref', $this->our_ref])
            ->andFilterWhere(['like', 'from_name', $this->from_name])
            ->andFilterWhere(['like', 'tel', $this->tel])
            ->andFilterWhere(['like', 'ref_no', $this->ref_no])
            ->andFilterWhere(['like', 'page_no', $this->page_no]);

        return $dataProvider;
    }
}
