<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\PurchaseMaster;

/**
 * PurchaseMasterSearch represents the model behind the search form of `backend\models\PurchaseMaster`.
 */
class PurchaseMasterSearch extends PurchaseMaster
{
    public $date_from;
    public $date_to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['docnum', 'docdat', 'supcod', 'supnam', 'job_no', 'paytrm', 'duedat', 'taxid', 'discod', 'addr01', 'addr02', 'addr03', 'zipcod', 'telnum', 'orgnum', 'refnum', 'vatdat', 'disc', 'remark', 'date_from', 'date_to'], 'safe'],
            [['vatpr0', 'amount', 'unitpr', 'vat_percent', 'vat_amount', 'tax_percent', 'tax_amount', 'total_amount'], 'number'],
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
        $query = PurchaseMaster::find();

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
            'docdat' => $this->docdat,
            'duedat' => $this->duedat,
            'vatdat' => $this->vatdat,
            'vatpr0' => $this->vatpr0,
            'amount' => $this->amount,
            'unitpr' => $this->unitpr,
            'vat_percent' => $this->vat_percent,
            'vat_amount' => $this->vat_amount,
            'tax_percent' => $this->tax_percent,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'docnum', $this->docnum])
            ->andFilterWhere(['like', 'supcod', $this->supcod])
            ->andFilterWhere(['like', 'supnam', $this->supnam])
            ->andFilterWhere(['like', 'job_no', $this->job_no])
            ->andFilterWhere(['like', 'paytrm', $this->paytrm])
            ->andFilterWhere(['like', 'taxid', $this->taxid])
            ->andFilterWhere(['like', 'discod', $this->discod])
            ->andFilterWhere(['like', 'addr01', $this->addr01])
            ->andFilterWhere(['like', 'addr02', $this->addr02])
            ->andFilterWhere(['like', 'addr03', $this->addr03])
            ->andFilterWhere(['like', 'zipcod', $this->zipcod])
            ->andFilterWhere(['like', 'telnum', $this->telnum])
            ->andFilterWhere(['like', 'orgnum', $this->orgnum])
            ->andFilterWhere(['like', 'refnum', $this->refnum])
            ->andFilterWhere(['like', 'disc', $this->disc])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        // ค้นหาตามช่วงวันที่
        if ($this->date_from) {
            $query->andFilterWhere(['>=', 'docdat', $this->date_from]);
        }

        if ($this->date_to) {
            $query->andFilterWhere(['<=', 'docdat', $this->date_to]);
        }

        return $dataProvider;
    }
}