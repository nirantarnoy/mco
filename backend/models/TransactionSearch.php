<?php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TransactionSearch represents the model behind the search form of JournalTransX.
 */
class TransactionSearch extends JournalTrans
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'trans_type_id', 'stock_type_id', 'customer_id', 'status', 'warehouse_id', 'trans_ref_id', 'party_id', 'party_type_id'], 'integer'],
            [['trans_date', 'journal_no', 'customer_name', 'remark'], 'safe'],
            [['qty'], 'number'],
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
     */
    public function search($params)
    {
        $query = JournalTrans::find()
            ->where(['in', 'trans_type_id', [3, 4, 5, 6]]) // Only transaction types 3-6
            ->with(['journalTransLines', 'warehouse']);

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
            return $dataProvider;
        }

        // Grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'trans_type_id' => $this->trans_type_id,
            'stock_type_id' => $this->stock_type_id,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'warehouse_id' => $this->warehouse_id,
            'trans_ref_id' => $this->trans_ref_id,
            'party_id' => $this->party_id,
            'party_type_id' => $this->party_type_id,
            'qty' => $this->qty,
        ]);

        $query->andFilterWhere(['like', 'journal_no', $this->journal_no])
            ->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        if (!empty($this->trans_date)) {
            $query->andFilterWhere(['>=', 'trans_date', $this->trans_date . ' 00:00:00'])
                ->andFilterWhere(['<=', 'trans_date', $this->trans_date . ' 23:59:59']);
        }

        return $dataProvider;
    }

    /**
     * Get transaction type options
     */
    public static function getTransactionTypeOptions()
    {
        return [
            3 => 'เบิกสินค้า',
            4 => 'คืนเบิก',
            5 => 'ยืมสินค้า',
            6 => 'คืนยืม',
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            0 => 'รอดำเนินการ',
            1 => 'อนุมัติ',
            2 => 'ไม่อนุมัติ',
        ];
    }
}