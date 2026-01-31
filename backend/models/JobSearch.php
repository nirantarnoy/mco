<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Job;

/**
 * JobSearch represents the model behind the search form of `backend\models\Job`.
 */
class JobSearch extends Job
{
    public $globalSearch;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'quotation_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['job_no', 'job_date'], 'safe'],
            [['job_amount'], 'number'],
            [['globalSearch'], 'string'],
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
        $query = Job::find()->alias('job');
        
        // Use leftJoin explicitly to ensure it doesn't get converted or affected by relation logic
        $query->leftJoin('quotation', 'job.quotation_id = quotation.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'job.id' => $this->id,
            'job.job_date' => $this->job_date,
            'job.status' => $this->status,
            'job.job_amount' => $this->job_amount,
            'job.created_at' => $this->created_at,
            'job.created_by' => $this->created_by,
            'job.updated_at' => $this->updated_at,
            'job.updated_by' => $this->updated_by,
        ]);

        // If quotation_id is '0' or specifies a value, filter it. 
        // If it's empty/null, we don't filter to show everything.
        if ($this->quotation_id !== null && $this->quotation_id !== '') {
             $query->andFilterWhere(['job.quotation_id' => $this->quotation_id]);
        }

        $query->andFilterWhere(['job.company_id' => \Yii::$app->session->get('company_id')]);
        $query->andFilterWhere(['or', ['!=', 'job.status', 500], ['job.status' => null]]);

        if($this->globalSearch != ''){
            $query->andFilterWhere(['or',
                ['like', 'job.job_no', $this->globalSearch],
                ['like', 'job.cus_po_no', $this->globalSearch],
                ['like', 'quotation.quotation_no', $this->globalSearch],
                ['like', 'quotation.customer_name', $this->globalSearch],
            ]);
        }

        return $dataProvider;
    }
}
