<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\PaymentReceipt;

/**
 * PaymentReceiptSearch represents the model behind the search form of `backend\models\PaymentReceipt`.
 */
class PaymentReceiptSearch extends PaymentReceipt
{
    // Additional search attributes
    public $payment_date_from;
    public $payment_date_to;
    public $amount_from;
    public $amount_to;
    public $created_date_from;
    public $created_date_to;
    public $has_attachment;
    public $customer_name;
    public $billing_number;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'billing_invoice_id', 'job_id', 'received_by', 'created_by', 'updated_by', 'status', 'has_attachment'], 'integer'],
            [['receipt_number', 'payment_method', 'bank_name', 'account_number', 'cheque_number', 'payment_status', 'attachment_path', 'attachment_name', 'notes', 'customer_name', 'billing_number'], 'safe'],
            [['payment_date', 'cheque_date', 'created_at', 'updated_at', 'payment_date_from', 'payment_date_to', 'created_date_from', 'created_date_to'], 'safe'],
            [['received_amount', 'discount_amount', 'vat_amount', 'withholding_tax', 'net_amount', 'remaining_balance', 'amount_from', 'amount_to'], 'number'],
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
        $query = PaymentReceipt::find();

        // add conditions that should always apply here
        $query->joinWith([
            'billingInvoice' => function($q) {
                $q->joinWith('customer');
            }
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'receipt_number',
                    'payment_date',
                    'received_amount',
                    'net_amount',
                    'payment_method',
                    'payment_status',
                    'created_at',
                    'updated_at',
                    // Custom sort attributes
                    'customer_name' => [
                        'asc' => ['customer.name' => SORT_ASC],
                        'desc' => ['customer.name' => SORT_DESC],
                    ],
                    'billing_number' => [
                        'asc' => ['billing_invoices.billing_number' => SORT_ASC],
                        'desc' => ['billing_invoices.billing_number' => SORT_DESC],
                    ],
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
            'payment_receipts.id' => $this->id,
            'payment_receipts.billing_invoice_id' => $this->billing_invoice_id,
            'payment_receipts.job_id' => $this->job_id,
            'payment_receipts.payment_date' => $this->payment_date,
            'payment_receipts.cheque_date' => $this->cheque_date,
            'payment_receipts.received_amount' => $this->received_amount,
            'payment_receipts.discount_amount' => $this->discount_amount,
            'payment_receipts.vat_amount' => $this->vat_amount,
            'payment_receipts.withholding_tax' => $this->withholding_tax,
            'payment_receipts.net_amount' => $this->net_amount,
            'payment_receipts.remaining_balance' => $this->remaining_balance,
            'payment_receipts.received_by' => $this->received_by,
            'payment_receipts.created_by' => $this->created_by,
            'payment_receipts.updated_by' => $this->updated_by,
            'payment_receipts.status' => $this->status,
            'payment_receipts.payment_method' => $this->payment_method,
            'payment_receipts.payment_status' => $this->payment_status,
        ]);

        $query->andFilterWhere(['like', 'payment_receipts.receipt_number', $this->receipt_number])
            ->andFilterWhere(['like', 'payment_receipts.bank_name', $this->bank_name])
            ->andFilterWhere(['like', 'payment_receipts.account_number', $this->account_number])
            ->andFilterWhere(['like', 'payment_receipts.cheque_number', $this->cheque_number])
            ->andFilterWhere(['like', 'payment_receipts.attachment_path', $this->attachment_path])
            ->andFilterWhere(['like', 'payment_receipts.attachment_name', $this->attachment_name])
            ->andFilterWhere(['like', 'payment_receipts.notes', $this->notes]);

        // Date range filters
        if (!empty($this->payment_date_from)) {
            $query->andFilterWhere(['>=', 'payment_receipts.payment_date', $this->payment_date_from]);
        }

        if (!empty($this->payment_date_to)) {
            $query->andFilterWhere(['<=', 'payment_receipts.payment_date', $this->payment_date_to]);
        }

        if (!empty($this->created_date_from)) {
            $query->andFilterWhere(['>=', 'DATE(payment_receipts.created_at)', $this->created_date_from]);
        }

        if (!empty($this->created_date_to)) {
            $query->andFilterWhere(['<=', 'DATE(payment_receipts.created_at)', $this->created_date_to]);
        }

        // Amount range filters
        if (!empty($this->amount_from)) {
            $query->andFilterWhere(['>=', 'payment_receipts.net_amount', $this->amount_from]);
        }

        if (!empty($this->amount_to)) {
            $query->andFilterWhere(['<=', 'payment_receipts.net_amount', $this->amount_to]);
        }

        // Attachment filter
        if ($this->has_attachment !== '' && $this->has_attachment !== null) {
            if ($this->has_attachment == 1) {
                $query->andFilterWhere(['IS NOT', 'payment_receipts.attachment_path', null])
                    ->andFilterWhere(['!=', 'payment_receipts.attachment_path', '']);
            } else {
                $query->andWhere([
                    'or',
                    ['payment_receipts.attachment_path' => null],
                    ['payment_receipts.attachment_path' => '']
                ]);
            }
        }

        // Customer name filter (จากตาราง customer ผ่าน billing_invoice)
        if (!empty($this->customer_name)) {
            $query->andFilterWhere(['like', 'customer.name', $this->customer_name]);
        }

        // Billing number filter
        if (!empty($this->billing_number)) {
            $query->andFilterWhere(['like', 'billing_invoices.billing_number', $this->billing_number]);
        }

        return $dataProvider;
    }

    /**
     * Search for dashboard statistics
     */
    public function searchForDashboard($params = [])
    {
        $query = PaymentReceipt::find();

        $this->load($params);

        // Apply default filters if any
        $query->andFilterWhere([
            'payment_receipts.status' => 1, // Active records only
        ]);

        // Apply date filters
        if (!empty($this->payment_date_from)) {
            $query->andFilterWhere(['>=', 'payment_receipts.payment_date', $this->payment_date_from]);
        }

        if (!empty($this->payment_date_to)) {
            $query->andFilterWhere(['<=', 'payment_receipts.payment_date', $this->payment_date_to]);
        }

        return $query;
    }

    /**
     * Get quick stats
     */
    public function getQuickStats()
    {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        return [
            'today_amount' => PaymentReceipt::find()
                    ->where(['payment_date' => $today])
                    ->sum('net_amount') ?? 0,

            'today_count' => PaymentReceipt::find()
                ->where(['payment_date' => $today])
                ->count(),

            'month_amount' => PaymentReceipt::find()
                    ->where(['>=', 'payment_date', $monthStart])
                    ->where(['<=', 'payment_date', $monthEnd])
                    ->sum('net_amount') ?? 0,

            'month_count' => PaymentReceipt::find()
                ->where(['>=', 'payment_date', $monthStart])
                ->where(['<=', 'payment_date', $monthEnd])
                ->count(),

            'total_amount' => PaymentReceipt::find()
                    ->sum('net_amount') ?? 0,

            'total_count' => PaymentReceipt::find()->count(),
        ];
    }

    /**
     * Get payment methods statistics
     */
    public function getPaymentMethodStats($dateFrom = null, $dateTo = null)
    {
        $query = PaymentReceipt::find()
            ->select(['payment_method', 'COUNT(*) as count', 'SUM(net_amount) as total_amount'])
            ->groupBy('payment_method');

        if ($dateFrom) {
            $query->andWhere(['>=', 'payment_date', $dateFrom]);
        }

        if ($dateTo) {
            $query->andWhere(['<=', 'payment_date', $dateTo]);
        }

        return $query->asArray()->all();
    }

    /**
     * Get daily payment summary
     */
    public function getDailySummary($days = 30)
    {
        $dateFrom = date('Y-m-d', strtotime("-{$days} days"));

        return PaymentReceipt::find()
            ->select([
                'DATE(payment_date) as date',
                'COUNT(*) as count',
                'SUM(net_amount) as total_amount',
                'AVG(net_amount) as avg_amount'
            ])
            ->where(['>=', 'payment_date', $dateFrom])
            ->groupBy('DATE(payment_date)')
            ->orderBy('DATE(payment_date) DESC')
            ->asArray()
            ->all();
    }

    /**
     * Get top customers by payment amount
     */
    public function getTopCustomers($limit = 10, $dateFrom = null, $dateTo = null)
    {
        $query = PaymentReceipt::find()
            ->select([
                'customer.id',
                'customer.name as customer_name',
                'COUNT(*) as payment_count',
                'SUM(payment_receipts.net_amount) as total_amount'
            ])
            ->joinWith(['billingInvoice.customer'])
            ->groupBy('customer.id')
            ->orderBy('total_amount DESC')
            ->limit($limit);

        if ($dateFrom) {
            $query->andWhere(['>=', 'payment_receipts.payment_date', $dateFrom]);
        }

        if ($dateTo) {
            $query->andWhere(['<=', 'payment_receipts.payment_date', $dateTo]);
        }

        return $query->asArray()->all();
    }

    /**
     * Search for export
     */
    public function searchForExport($params)
    {
        $query = PaymentReceipt::find();

        $query->joinWith([
            'billingInvoice' => function($q) {
                $q->joinWith('customer');
            },
            'receivedBy',
            'createdBy'
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $query;
        }

        // Apply all filters same as search() method
        $query->andFilterWhere([
            'payment_receipts.id' => $this->id,
            'payment_receipts.billing_invoice_id' => $this->billing_invoice_id,
            'payment_receipts.job_id' => $this->job_id,
            'payment_receipts.payment_method' => $this->payment_method,
            'payment_receipts.payment_status' => $this->payment_status,
            'payment_receipts.received_by' => $this->received_by,
            'payment_receipts.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'payment_receipts.receipt_number', $this->receipt_number])
            ->andFilterWhere(['like', 'payment_receipts.bank_name', $this->bank_name])
            ->andFilterWhere(['like', 'payment_receipts.cheque_number', $this->cheque_number])
            ->andFilterWhere(['like', 'payment_receipts.notes', $this->notes]);

        // Date filters
        if (!empty($this->payment_date_from)) {
            $query->andFilterWhere(['>=', 'payment_receipts.payment_date', $this->payment_date_from]);
        }

        if (!empty($this->payment_date_to)) {
            $query->andFilterWhere(['<=', 'payment_receipts.payment_date', $this->payment_date_to]);
        }

        // Amount filters
        if (!empty($this->amount_from)) {
            $query->andFilterWhere(['>=', 'payment_receipts.net_amount', $this->amount_from]);
        }

        if (!empty($this->amount_to)) {
            $query->andFilterWhere(['<=', 'payment_receipts.net_amount', $this->amount_to]);
        }

        // Customer and billing filters
        if (!empty($this->customer_name)) {
            $query->andFilterWhere(['like', 'customer.name', $this->customer_name]);
        }

        if (!empty($this->billing_number)) {
            $query->andFilterWhere(['like', 'billing_invoices.billing_number', $this->billing_number]);
        }

        // Attachment filter
        if ($this->has_attachment !== '' && $this->has_attachment !== null) {
            if ($this->has_attachment == 1) {
                $query->andFilterWhere(['IS NOT', 'payment_receipts.attachment_path', null])
                    ->andFilterWhere(['!=', 'payment_receipts.attachment_path', '']);
            } else {
                $query->andWhere([
                    'or',
                    ['payment_receipts.attachment_path' => null],
                    ['payment_receipts.attachment_path' => '']
                ]);
            }
        }

        return $query;
    }

    /**
     * Get attribute labels for search form
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        return array_merge($labels, [
            'payment_date_from' => 'วันที่รับเงิน (จาก)',
            'payment_date_to' => 'วันที่รับเงิน (ถึง)',
            'amount_from' => 'จำนวนเงิน (จาก)',
            'amount_to' => 'จำนวนเงิน (ถึง)',
            'created_date_from' => 'วันที่สร้าง (จาก)',
            'created_date_to' => 'วันที่สร้าง (ถึง)',
            'has_attachment' => 'ไฟล์แนบ',
            'customer_name' => 'ชื่อลูกค้า',
            'billing_number' => 'เลขที่ใบแจ้งหนี้',
        ]);
    }
}