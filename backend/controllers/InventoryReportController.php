<?php

namespace backend\controllers;

use Yii;
use backend\models\StockCardSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * InventoryReportController implements the report actions for Inventory management.
 */
class InventoryReportController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Display stock card report for accountants
     * @return mixed
     */
    public function actionStockCard()
    {
        $searchModel = new StockCardSearch();
        $params = Yii::$app->request->queryParams;
        $searchModel->load($params);

        // Perform search if parameters provided
        $results = [];
        if (!empty($params)) {
             $results = $searchModel->getData();
        }

        return $this->render('stock-card', [
            'searchModel' => $searchModel,
            'results' => $results,
        ]);
    }

    /**
     * Export stock card report to CSV (Excel format)
     */
    public function actionExportExcel()
    {
        $searchModel = new StockCardSearch();
        $params = Yii::$app->request->queryParams;
        $searchModel->load($params);

        $results = $searchModel->getData();
        $transactions = $results['transactions'];
        $runningBalance = $results['initialBalance'];

        $filename = 'stock_card_report_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // Add BOM for UTF-8 compatibility with Thai characters in Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header Row 1
        fputcsv($output, [
            'สินค้า', 'วันที่ทำรายการ', 'หน่วย', 
            'ซื้อ', 'ส่งคืน (Out)', 'ราคาต่อหน่วย (ซื้อ)', 'รวมซื้อ/ส่งคืน',
            'Ref No.',
            'ขาย', 'ของแถม', 'รับคืน (In)', 'รับคืนของแถม', 'ราคาต่อหน่วย (ขาย)', 'รวมขาย/ของแถม',
            'คงเหลือ', 'หมายเหตุ'
        ]);

        if ($runningBalance != 0) {
            fputcsv($output, [
                '', '', 'ยอดยกมา', 
                '', '', '', '', 
                '', 
                '', '', '', '', '', '', 
                $runningBalance, ''
            ]);
        }

        foreach ($transactions as $trans) {
            $unitPrice = ($trans->qty != 0) ? ($trans->line_price / $trans->qty) : 0;
            
            $colBuy = ''; $colReturnPurch = ''; $colPurchPrice = ''; $colPurchTotal = '';
            $colSale = ''; $colFree = ''; $colReturnSale = ''; $colReturnFree = ''; $colSalePrice = ''; $colSaleTotal = '';
            
            switch ($trans->trans_type_id) {
                case 1: $colBuy = $trans->qty; $colPurchPrice = $unitPrice; $colPurchTotal = $trans->line_price; $runningBalance += $trans->qty; break;
                case 2: $colReturnPurch = $trans->qty; $colPurchPrice = $unitPrice; $colPurchTotal = $trans->line_price; $runningBalance -= $trans->qty; break;
                case 3: 
                    if ($unitPrice == 0) $colFree = $trans->qty; else $colSale = $trans->qty;
                    $colSalePrice = $unitPrice; $colSaleTotal = $trans->line_price; $runningBalance -= $trans->qty; break;
                case 4: 
                    if ($unitPrice == 0) $colReturnFree = $trans->qty; else $colReturnSale = $trans->qty;
                    $colSalePrice = $unitPrice; $colSaleTotal = $trans->line_price; $runningBalance += $trans->qty; break;
                case 8: 
                    if ($trans->stock_type_id == 1) { $colBuy = $trans->qty; $runningBalance += $trans->qty; } else { $colSale = $trans->qty; $runningBalance -= $trans->qty; } break;
            }

            fputcsv($output, [
                $trans->product->code . ' - ' . $trans->product->name,
                date('d/m/Y', strtotime($trans->trans_date)),
                $trans->product->unit->name ?? 'ชิ้น',
                $colBuy, $colReturnPurch, $colPurchPrice, $colPurchTotal,
                $trans->journalTrans->journal_no ?? '-',
                $colSale, $colFree, $colReturnSale, $colReturnFree, $colSalePrice, $colSaleTotal,
                $runningBalance, $trans->remark
            ]);
        }

        fclose($output);
        exit;
    }
}
