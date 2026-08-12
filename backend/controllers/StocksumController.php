<?php

namespace backend\controllers;

use backend\models\Stocksum;
use backend\models\StocksumSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

/**
 * StocksumController implements the CRUD actions for Stocksum model.
 */
class StocksumController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Stocksum models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StocksumSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Stocksum model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Stocksum model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Stocksum();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Stocksum model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Stocksum model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionProcessSnapshot()
    {
        $period = \Yii::$app->request->post('snapshot_period');
        if (!$period) {
            \Yii::$app->session->setFlash('error', 'กรุณาระบุเดือนที่ต้องการประมวลผล');
            return $this->redirect(['stock-report']);
        }
        
        // Auto-create table if not exists
        \Yii::$app->db->createCommand("
            CREATE TABLE IF NOT EXISTS `stock_monthly_snapshot` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `warehouse_id` int(11) NOT NULL,
                `lot_no` varchar(50) DEFAULT NULL,
                `qty` float DEFAULT '0',
                `snapshot_period` varchar(7) NOT NULL COMMENT 'Format YYYY-MM',
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx-stock_monthly_snapshot-period` (`snapshot_period`),
                KEY `idx-stock_monthly_snapshot-product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ")->execute();

        // Calculate end of the month date
        $dateEnd = date('Y-m-t 23:59:59', strtotime($period . '-01'));

        $models = \backend\models\StockSum::find()->all();
        
        $adjustments = \backend\models\JournalTransLine::find()
            ->select([
                'journal_trans_line.product_id', 
                'journal_trans_line.lot_no',
                'SUM(CASE WHEN journal_trans.stock_type_id = 1 THEN journal_trans_line.qty ELSE -journal_trans_line.qty END) as qty_diff'
            ])
            ->joinWith('journalTrans')
            ->where(['>', 'journal_trans.trans_date', $dateEnd])
            ->andWhere(['IN', 'journal_trans.status', [0, 1, 2]])
            ->groupBy(['journal_trans_line.product_id', 'journal_trans_line.lot_no'])
            ->asArray()
            ->all();

        $adjMap = [];
        foreach ($adjustments as $adj) {
            $key = $adj['product_id'] . '_' . trim((string)$adj['lot_no']);
            $adjMap[$key] = (float)$adj['qty_diff'];
        }

        // Delete existing snapshot for this period
        \backend\models\StockMonthlySnapshot::deleteAll(['snapshot_period' => $period]);

        $batchInsert = [];
        $createdAt = date('Y-m-d H:i:s');
        foreach ($models as $model) {
            $key = $model->product_id . '_' . trim((string)$model->lot_no);
            $historicalQty = $model->qty;
            if (isset($adjMap[$key])) {
                $historicalQty = $model->qty - $adjMap[$key];
            }

            if ($historicalQty != 0) { // Keep only non-zero to save space
                $batchInsert[] = [
                    $model->product_id,
                    $model->warehouse_id,
                    $model->lot_no,
                    $historicalQty,
                    $period,
                    $createdAt
                ];
            }
        }

        if (!empty($batchInsert)) {
            \Yii::$app->db->createCommand()->batchInsert(
                'stock_monthly_snapshot',
                ['product_id', 'warehouse_id', 'lot_no', 'qty', 'snapshot_period', 'created_at'],
                $batchInsert
            )->execute();
        }

        \Yii::$app->session->setFlash('success', 'ประมวลผลยอดยกไปของเดือน ' . $period . ' สำเร็จเรียบร้อยแล้ว');
        return $this->redirect(['stock-report']);
    }

    public function actionStockReport()
    {
        $filter_qty = \Yii::$app->request->get('filter_qty');
        $export = \Yii::$app->request->get('export');
        $product_group_id = \Yii::$app->request->get('product_group_id');
        $as_of_date = \Yii::$app->request->get('as_of_date');
        $snapshot_period = \Yii::$app->request->get('snapshot_period');

        if ($snapshot_period) {
            // Read from snapshot table instead
            $query = \backend\models\StockMonthlySnapshot::find()
                ->joinWith(['product', 'product.productGroup', 'product.unit'])
                ->where(['snapshot_period' => $snapshot_period])
                ->orderBy(['product.product_group_id' => SORT_ASC, 'product.code' => SORT_ASC, 'stock_monthly_snapshot.lot_no' => SORT_ASC]);

            if ($filter_qty === 'gt0') {
                $query->andWhere(['>', 'stock_monthly_snapshot.qty', 0]);
            } elseif ($filter_qty === 'eq0') {
                $query->andWhere(['<=', 'stock_monthly_snapshot.qty', 0]);
            }
            if ($product_group_id) {
                $query->andWhere(['product.product_group_id' => $product_group_id]);
            }
            $models = $query->all();
        } else {
            // Read from current stock_sum
            $query = \backend\models\StockSum::find()
                ->joinWith(['product', 'product.productGroup', 'product.unit'])
                ->orderBy(['product.product_group_id' => SORT_ASC, 'product.code' => SORT_ASC, 'stock_sum.lot_no' => SORT_ASC]);
            
            if (!$as_of_date) {
                if ($filter_qty === 'gt0') {
                    $query->andWhere(['>', 'stock_sum.qty', 0]);
                } elseif ($filter_qty === 'eq0') {
                    $query->andWhere(['<=', 'stock_sum.qty', 0]);
                }
            }

            if ($product_group_id) {
                $query->andWhere(['product.product_group_id' => $product_group_id]);
            }

            $models = $query->all();

            if ($as_of_date) {
                $dateEnd = date('Y-m-d 23:59:59', strtotime($as_of_date));
                
                // Get all transactions AFTER the as_of_date
                $adjustments = \backend\models\JournalTransLine::find()
                    ->select([
                        'journal_trans_line.product_id', 
                        'journal_trans_line.lot_no',
                        'SUM(CASE WHEN journal_trans.stock_type_id = 1 THEN journal_trans_line.qty ELSE -journal_trans_line.qty END) as qty_diff'
                    ])
                    ->joinWith('journalTrans')
                    ->where(['>', 'journal_trans.trans_date', $dateEnd])
                    ->andWhere(['IN', 'journal_trans.status', [0, 1, 2]]) // Adjust status based on typical active trans
                    ->groupBy(['journal_trans_line.product_id', 'journal_trans_line.lot_no'])
                    ->asArray()
                    ->all();

                $adjMap = [];
                foreach ($adjustments as $adj) {
                    $key = $adj['product_id'] . '_' . trim((string)$adj['lot_no']);
                    $adjMap[$key] = (float)$adj['qty_diff'];
                }

                $filteredModels = [];
                foreach ($models as $model) {
                    $key = $model->product_id . '_' . trim((string)$model->lot_no);
                    
                    // Historical = Current - (IN after) + (OUT after) = Current - (qty_diff)
                    if (isset($adjMap[$key])) {
                        $model->qty = $model->qty - $adjMap[$key];
                    }

                    // Apply filter_qty manually
                    if ($filter_qty === 'gt0' && $model->qty <= 0) {
                        continue;
                    }
                    if ($filter_qty === 'eq0' && $model->qty > 0) {
                        continue;
                    }
                    
                    $filteredModels[] = $model;
                }
                $models = $filteredModels;
            }
        }

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
        ]);

        if ($export === 'excel') {
            return $this->exportToExcel($models, $filter_qty);
        }

        // Get available snapshot periods for dropdown
        $availableSnapshots = [];
        try {
            $periods = \Yii::$app->db->createCommand('SELECT DISTINCT snapshot_period FROM stock_monthly_snapshot ORDER BY snapshot_period DESC')->queryColumn();
            foreach ($periods as $p) {
                $availableSnapshots[$p] = 'สิ้นเดือน ' . $p;
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        return $this->render('stock_report', [
            'dataProvider' => $dataProvider,
            'filter_qty' => $filter_qty,
            'product_group_id' => $product_group_id,
            'as_of_date' => $as_of_date,
            'snapshot_period' => $snapshot_period,
            'availableSnapshots' => $availableSnapshots,
        ]);
    }

    public function actionBorrowReturnReport()
    {
        $job_id = $this->request->get('job_id');
        $product_id = $this->request->get('product_id');
        $from_date = $this->request->get('from_date');
        $to_date = $this->request->get('to_date');

        $query = \backend\models\JournalTransLine::find()
            ->select([
                'MAX(journal_trans_line.id) AS id',
                't.job_id AS job_id',
                'journal_trans_line.product_id',
                'SUM(CASE WHEN t.trans_type_id = 3 THEN journal_trans_line.qty ELSE 0 END) as total_withdraw',
                'SUM(CASE WHEN t.trans_type_id = 4 THEN journal_trans_line.qty ELSE 0 END) as total_return_withdraw',
                'SUM(CASE WHEN t.trans_type_id = 5 THEN journal_trans_line.qty ELSE 0 END) as total_borrow',
                'SUM(CASE WHEN t.trans_type_id = 6 THEN journal_trans_line.qty ELSE 0 END) as total_return_borrow',
                'SUM(COALESCE(journal_trans_line.damaged_qty, 0)) as total_damaged',
                'SUM(COALESCE(journal_trans_line.missing_qty, 0)) as total_missing',
                'GROUP_CONCAT(DISTINCT journal_trans_line.condition_note SEPARATOR ", ") as remarks',
                '(SELECT SUM(qty) FROM stock_sum WHERE product_id = journal_trans_line.product_id) as current_stock_qty'
            ])
            ->joinWith('journalTrans t')
            ->where(['IN', 't.status', [0, 1, 2]])
            ->groupBy(['t.job_id', 'journal_trans_line.product_id']);

        if ($job_id) {
            $query->andWhere(['t.job_id' => $job_id]);
        }
        if ($product_id) {
            $query->andWhere(['journal_trans_line.product_id' => $product_id]);
        }
        if ($from_date) {
            $query->andWhere(['>=', 't.trans_date', $from_date]);
        }
        if ($to_date) {
            $query->andWhere(['<=', 't.trans_date', $to_date]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query->asArray(),
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('borrow_return_report', [
            'dataProvider' => $dataProvider,
            'job_id' => $job_id,
            'product_id' => $product_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ]);
    }

    /**
     * Finds the Stocksum model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Stocksum the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Stocksum::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function exportToExcel($stockSums, $filter_qty)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $filename = 'stock_report_' . date('Y-m-d_His') . '.xlsx';

        \Yii::$app->response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        \Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="' . $filename . '"');
        \Yii::$app->response->headers->add('Cache-Control', 'max-age=0');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'รายงานแสดงยอดสินค้าคงเหลือ (แยก Lot)');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Filters text
        $filter_text = 'แสดงทั้งหมด';
        if ($filter_qty == 'gt0') {
            $filter_text = 'แสดงคงเหลือ > 0';
        } elseif ($filter_qty == 'eq0') {
            $filter_text = 'แสดงคงเหลือ = 0';
        }
        $sheet->setCellValue('A2', 'เงื่อนไข: ' . $filter_text);
        $sheet->mergeCells('A2:H2');

        // Headers
        $headers = ['หมวดหมู่สินค้า', 'รหัสสินค้า', 'รายการสินค้า', 'Lot No.', 'หน่วยนับ', 'คงเหลือ', 'ราคาต่อหน่วย', 'มูลค่าคงเหลือ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $sheet->getStyle($col . '4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE9ECEF');
            $col++;
        }

        $row = 5;
        $current_group = null;
        $group_qty = 0;
        $group_value = 0;
        $total_qty = 0;
        $total_value = 0;

        foreach ($stockSums as $stockSum) {
            $product = $stockSum->product;
            if (!$product) continue;
            
            $group_name = $product->productGroup ? $product->productGroup->name : 'ไม่ระบุหมวดหมู่';

            $receiveLine = \backend\models\JournalTransLine::find()
                ->joinWith('journalTrans')
                ->where(['journal_trans_line.product_id' => $product->id])
                ->andWhere(['journal_trans_line.lot_no' => $stockSum->lot_no])
                ->andWhere(['journal_trans.trans_type_id' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE])
                ->one();
            
            $unit_price = $receiveLine && $receiveLine->sale_price > 0 ? $receiveLine->sale_price : 0;
            
            if ($unit_price <= 0) {
                $unit_price = $product->cost_price > 0 ? $product->cost_price : ($product->sale_price > 0 ? $product->sale_price : 0);
            }
            
            // If still 0, try to get the latest PO price for this product as last resort
            if ($unit_price <= 0) {
                $latestReceive = \backend\models\JournalTransLine::find()
                    ->joinWith('journalTrans')
                    ->where(['journal_trans_line.product_id' => $product->id])
                    ->andWhere(['journal_trans.trans_type_id' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE])
                    ->andWhere(['>', 'journal_trans_line.sale_price', 0])
                    ->orderBy(['journal_trans.id' => SORT_DESC])
                    ->one();
                if ($latestReceive) {
                    $unit_price = $latestReceive->sale_price;
                }
            }

            $balance_value = $stockSum->qty * floatval($unit_price);

            if ($current_group !== null && $current_group !== $group_name) {
                $sheet->setCellValue('A' . $row, 'รวมหมวดนี้');
                $sheet->mergeCells('A' . $row . ':E' . $row);
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue('F' . $row, $group_qty);
                $sheet->setCellValue('H' . $row, $group_value);
                $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF4F4F4');
                $row++;

                $group_qty = 0;
                $group_value = 0;
            }

            $current_group = $group_name;
            $group_qty += $stockSum->qty;
            $group_value += $balance_value;
            $total_qty += $stockSum->qty;
            $total_value += $balance_value;

            $sheet->setCellValueExplicit('A' . $row, $group_name, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $row, $product->code, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $product->name);
            $sheet->setCellValue('D' . $row, $stockSum->lot_no ?: '-');
            $sheet->setCellValue('E' . $row, $product->unit ? $product->unit->name : '');
            $sheet->setCellValue('F' . $row, $stockSum->qty);
            $sheet->setCellValue('G' . $row, $unit_price);
            $sheet->setCellValue('H' . $row, $balance_value);
            
            $sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            
            $row++;
        }

        if ($current_group !== null) {
            $sheet->setCellValue('A' . $row, 'รวมหมวดนี้');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('F' . $row, $group_qty);
            $sheet->setCellValue('H' . $row, $group_value);
            $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF4F4F4');
            $sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        // Total
        $sheet->setCellValue('A' . $row, 'รวมทั้งสิ้น');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('F' . $row, $total_qty);
        $sheet->setCellValue('H' . $row, $total_value);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');
        $sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $dataRange = 'A4:H' . $row;
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('000000');

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $content;
    }
}
