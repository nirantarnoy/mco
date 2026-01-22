<?php
// backend/models/search/BillingInvoiceSearch.php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\BillingInvoice;

class BillingInvoiceSearch extends BillingInvoice
{
    public function rules()
    {
        return [
            [['id', 'customer_id'], 'integer'],
            [['billing_number', 'billing_date', 'status', 'created_at'], 'safe'],
            [['total_amount'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = BillingInvoice::find()->with('customer');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'billing_date' => $this->billing_date,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
        ]);
        $company_id = \Yii::$app->session->get('company_id');
        if ($company_id != 1) {
            $query->andFilterWhere(['company_id' => $company_id]);
        }

        $query->andFilterWhere(['like', 'billing_number', $this->billing_number]);

        if ($this->created_at) {
            $query->andFilterWhere(['like', 'created_at', $this->created_at]);
        }

        return $dataProvider;
    }
}