<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Invoice;

/**
 * InvoiceSearch represents the model behind the search form of `backend\models\Invoice`.
 */
class InvoiceSearch extends Invoice
{
    public $globalSearch;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_by', 'updated_by', 'job_id', 'payment_term_id', 'quotation_id', 'pay_for_emp_id', 'customer_id'], 'integer'],
            [['invoice_type', 'invoice_number', 'invoice_date', 'customer_code', 'customer_name', 'customer_address', 'customer_tax_id', 'po_number', 'po_date', 'credit_terms', 'due_date', 'total_amount_text', 'payment_due_date', 'check_due_date', 'notes', 'created_at', 'updated_at', 'special_note', 'globalSearch'], 'safe'],
            [['subtotal', 'discount_percent', 'discount_amount', 'vat_percent', 'vat_amount', 'total_amount'], 'number'],
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
        $query = Invoice::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'invoice_date' => SORT_DESC,
                    'id' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
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
            'status' => Invoice::STATUS_ACTIVE,
            'subtotal' => $this->subtotal,
            'discount_percent' => $this->discount_percent,
            'discount_amount' => $this->discount_amount,
            'vat_percent' => $this->vat_percent,
            'vat_amount' => $this->vat_amount,
            'total_amount' => $this->total_amount,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'job_id' => $this->job_id,
            'payment_term_id' => $this->payment_term_id,
            'quotation_id' => $this->quotation_id,
            'pay_for_emp_id' => $this->pay_for_emp_id,
            'customer_id' => $this->customer_id,
        ]);

        $company_id = \Yii::$app->session->get('company_id');
        if ($company_id != 1) {
            $query->andFilterWhere(['company_id' => $company_id]);
        }

        $query->andFilterWhere(['like', 'invoice_type', $this->invoice_type])
            ->andFilterWhere(['like', 'invoice_number', $this->invoice_number])
            ->andFilterWhere(['like', 'customer_code', $this->customer_code])
            ->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'customer_address', $this->customer_address])
            ->andFilterWhere(['like', 'customer_tax_id', $this->customer_tax_id])
            ->andFilterWhere(['like', 'po_number', $this->po_number])
            ->andFilterWhere(['like', 'credit_terms', $this->credit_terms])
            ->andFilterWhere(['like', 'total_amount_text', $this->total_amount_text])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'special_note', $this->special_note]);

        if ($this->invoice_date) {
            $query->andFilterWhere(['invoice_date' => $this->invoice_date]);
        }

        if ($this->globalSearch) {
            $query->andFilterWhere(['or',
                ['like', 'invoice_number', $this->globalSearch],
                ['like', 'customer_name', $this->globalSearch],
                ['like', 'customer_code', $this->globalSearch],
            ]);
        }

        return $dataProvider;
    }
}
