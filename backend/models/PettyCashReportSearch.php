<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * PettyCashReportSearch represents the model behind the search form for Petty Cash Report.
 */
class PettyCashReportSearch extends Model
{
    public $date_from;
    public $date_to;
    public $ac_code;
    public $vat_type; // 'all', 'vat', 'no_vat'
    public $report_type; // 'summary', 'detail'

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to'], 'safe'],
            [['ac_code'], 'string', 'max' => 50],
            [['vat_type', 'report_type'], 'string'],
            [['vat_type'], 'in', 'range' => ['all', 'vat', 'no_vat']],
            [['report_type'], 'in', 'range' => ['summary', 'detail']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'date_from' => 'วันที่เริ่มต้น',
            'date_to' => 'วันที่สิ้นสุด',
            'ac_code' => 'รหัสบัญชี (A/C Code)',
            'vat_type' => 'ประเภท VAT',
            'report_type' => 'ประเภทรายงาน',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider|SqlDataProvider
     */
    public function search($params)
    {
        // Set default values
        $this->date_from = $this->date_from ?: date('Y-m-01'); // First day of current month
        $this->date_to = $this->date_to ?: date('Y-m-t'); // Last day of current month
        $this->vat_type = $this->vat_type ?: 'all';
        $this->report_type = $this->report_type ?: 'detail';

        $this->load($params);

        if (!$this->validate()) {
            // Return empty data provider if validation fails
            return new ActiveDataProvider([
                'query' => PettyCashDetail::find()->where('0=1'),
                'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
            ]);
        }

        // Build query based on report type
        if ($this->report_type === 'summary') {
            return $this->getSummaryDataProvider();
        } else {
            return $this->getDetailDataProvider();
        }
    }

    /**
     * Get detail data provider using SQL query
     */
    protected function getDetailDataProvider()
    {
        // Build SQL query
        $sql = "SELECT 
                    d.*,
                    v.pcv_nox,
                    v.date as voucher_date,
                    v.name as voucher_name
                FROM petty_cash_detail d
                INNER JOIN petty_cash_voucher v ON d.voucher_id = v.id
                WHERE v.status = 1";

        $params = [];
        
        // Filter by date range
        if ($this->date_from) {
            $sql .= " AND v.date >= :date_from";
            $params[':date_from'] = $this->date_from;
        }
        if ($this->date_to) {
            $sql .= " AND v.date <= :date_to";
            $params[':date_to'] = $this->date_to;
        }

        // Filter by A/C Code
        if (!empty($this->ac_code)) {
            $sql .= " AND d.ac_code LIKE :ac_code";
            $params[':ac_code'] = '%' . $this->ac_code . '%';
        }

        // Filter by VAT type
        if ($this->vat_type === 'vat') {
            $sql .= " AND d.vat_amount > 0";
        } elseif ($this->vat_type === 'no_vat') {
            $sql .= " AND d.vat_amount = 0";
        }

        $sql .= " ORDER BY v.date ASC, v.id ASC, d.sort_order ASC";


        return new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    /**
     * Get summary data provider using SQL query
     */
    protected function getSummaryDataProvider()
    {
        // Build SQL query
        $sql = "SELECT 
                    COALESCE(d.ac_code, 'ไม่ระบุ') as ac_code,
                    SUM(d.amount) as total_amount,
                    SUM(d.vat_amount) as total_vat_amount,
                    SUM(d.wht) as total_wht,
                    SUM(d.other) as total_other,
                    SUM(d.total) as grand_total,
                    COUNT(*) as count_transactions
                FROM petty_cash_detail d
                INNER JOIN petty_cash_voucher v ON d.voucher_id = v.id
                WHERE v.status = 1";

        $params = [];
        
        // Filter by date range
        if ($this->date_from) {
            $sql .= " AND v.date >= :date_from";
            $params[':date_from'] = $this->date_from;
        }
        if ($this->date_to) {
            $sql .= " AND v.date <= :date_to";
            $params[':date_to'] = $this->date_to;
        }

        // Filter by A/C Code
        if (!empty($this->ac_code)) {
            $sql .= " AND d.ac_code LIKE :ac_code";
            $params[':ac_code'] = '%' . $this->ac_code . '%';
        }

        // Filter by VAT type
        if ($this->vat_type === 'vat') {
            $sql .= " AND d.vat_amount > 0";
        } elseif ($this->vat_type === 'no_vat') {
            $sql .= " AND d.vat_amount = 0";
        }

        $sql .= " GROUP BY d.ac_code ORDER BY d.ac_code ASC";

        return new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    /**
     * Get total summary for footer
     */
    public function getTotalSummary()
    {
        // Build SQL query
        $sql = "SELECT 
                    SUM(d.amount) as total_amount,
                    SUM(d.vat_amount) as total_vat_amount,
                    SUM(d.wht) as total_wht,
                    SUM(d.other) as total_other,
                    SUM(d.total) as grand_total,
                    COUNT(*) as count_transactions
                FROM petty_cash_detail d
                INNER JOIN petty_cash_voucher v ON d.voucher_id = v.id
                WHERE v.status = 1";

        $params = [];
        
        // Apply same filters
        if ($this->date_from) {
            $sql .= " AND v.date >= :date_from";
            $params[':date_from'] = $this->date_from;
        }
        if ($this->date_to) {
            $sql .= " AND v.date <= :date_to";
            $params[':date_to'] = $this->date_to;
        }
        if (!empty($this->ac_code)) {
            $sql .= " AND d.ac_code LIKE :ac_code";
            $params[':ac_code'] = '%' . $this->ac_code . '%';
        }
        if ($this->vat_type === 'vat') {
            $sql .= " AND d.vat_amount > 0";
        } elseif ($this->vat_type === 'no_vat') {
            $sql .= " AND d.vat_amount = 0";
        }

        $result = Yii::$app->db->createCommand($sql, $params)->queryOne();

        // Return default values if no result
        return $result ?: [
            'total_amount' => 0,
            'total_vat_amount' => 0,
            'total_wht' => 0,
            'total_other' => 0,
            'grand_total' => 0,
            'count_transactions' => 0,
        ];
    }
}