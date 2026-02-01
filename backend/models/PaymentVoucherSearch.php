<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\PaymentVoucher;

/**
 * PaymentVoucherSearch represents the model behind the search form of `backend\models\PaymentVoucher`.
 */
class PaymentVoucherSearch extends PaymentVoucher
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'recipient_id', 'payment_method', 'ref_id', 'ref_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'company_id'], 'integer'],
            [['voucher_no', 'trans_date', 'recipient_name', 'cheque_no', 'cheque_date', 'paid_for'], 'safe'],
            [['amount'], 'number'],
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
        $query = PaymentVoucher::find();

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
            'trans_date' => $this->trans_date,
            'recipient_id' => $this->recipient_id,
            'payment_method' => $this->payment_method,
            'cheque_date' => $this->cheque_date,
            'amount' => $this->amount,
            'ref_id' => $this->ref_id,
            'ref_type' => $this->ref_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'voucher_no', $this->voucher_no])
            ->andFilterWhere(['like', 'recipient_name', $this->recipient_name])
            ->andFilterWhere(['like', 'cheque_no', $this->cheque_no])
            ->andFilterWhere(['like', 'paid_for', $this->paid_for]);

        return $dataProvider;
    }
}
