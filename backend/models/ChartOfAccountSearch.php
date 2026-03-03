<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ChartOfAccount;

/**
 * ChartOfAccountSearch represents the model behind the search form of `backend\models\ChartOfAccount`.
 */
class ChartOfAccountSearch extends ChartOfAccount
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'account_level', 'account_type', 'parent_account_id', 'company_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['account_code', 'account_name', 'account_group'], 'safe'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = ChartOfAccount::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'account_level' => $this->account_level,
            'account_type' => $this->account_type,
            'parent_account_id' => $this->parent_account_id,
            'company_id' => $this->company_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'account_code', $this->account_code])
            ->andFilterWhere(['like', 'account_name', $this->account_name])
            ->andFilterWhere(['like', 'account_group', $this->account_group]);

        return $dataProvider;
    }
}
