<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\PurchPayment;

/**
 * PurchPaymentSearch represents the model behind the search form of `backend\models\PurchPayment`.
 */
class PurchPaymentSearch extends PurchPayment
{
    public $purch_no;
    public $vendor_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'purch_id', 'created_by'], 'integer'],
            [['trans_date', 'status', 'created_at', 'purch_no', 'vendor_name'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
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
        $query = PurchPayment::find();
        $query->joinWith(['purch']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $dataProvider->sort->attributes['purch_no'] = [
            'asc' => ['purch.purch_no' => SORT_ASC],
            'desc' => ['purch.purch_no' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['vendor_name'] = [
            'asc' => ['purch.vendor_name' => SORT_ASC],
            'desc' => ['purch.vendor_name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'purch_payment.id' => $this->id,
            'purch_payment.purch_id' => $this->purch_id,
            'purch_payment.created_by' => $this->created_by,
            'purch_payment.trans_date' => $this->trans_date,
            'purch_payment.created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'purch_payment.status', $this->status])
            ->andFilterWhere(['like', 'purch.purch_no', $this->purch_no])
            ->andFilterWhere(['like', 'purch.vendor_name', $this->vendor_name]);

        return $dataProvider;
    }
}