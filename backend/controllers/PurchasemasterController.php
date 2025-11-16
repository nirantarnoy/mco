<?php

namespace backend\controllers;

use backend\models\PurchaseMaster;
use backend\models\PurchaseDetail;
use backend\models\PurchaseMasterSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Yii;
use yii\db\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * PurchaseMasterController implements the CRUD actions for PurchaseMaster model.
 */
class PurchasemasterController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all PurchaseMaster models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseMasterSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchaseMaster model.
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
     * Creates a new PurchaseMaster model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new PurchaseMaster();
        $model->docnum = PurchaseMaster::generateDocnum();
        $model->docdat = date('Y-m-d');
        $model->vatdat = date('Y-m-d');
        $model->vat_percent = 7;
        $model->tax_percent = 0;

        if ($this->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {

                    // บันทึก Master
                    if ($model->save()) {

                        // บันทึก Details
                        $details = Yii::$app->request->post('PurchaseDetail', []);

                        if (!empty($details)) {
                            foreach ($details as $index => $detailData) {
                                if (!empty($detailData['stkcod']) || !empty($detailData['stkdes'])) {
                                    $detail = new PurchaseDetail();
                                    $detail->purchase_master_id = $model->id;
                                    $detail->line_no = $index + 1;
                                    $detail->stkcod = $detailData['stkcod'] ?? null;
                                    $detail->stkdes = $detailData['stkdes'] ?? null;
                                    $detail->uqnty = $detailData['uqnty'] ?? 0;
                                    $detail->unitpr = $detailData['unitpr'] ?? 0;
                                    $detail->disc = $detailData['disc'] ?? null;
                                    $detail->remark = $detailData['remark'] ?? null;

                                    // คำนวณยอดเงิน
                                    $detail->calculateAmount();

                                    if (!$detail->save()) {
                                        throw new Exception('ไม่สามารถบันทึกรายละเอียดสินค้าได้');
                                    }
                                }
                            }
                        }

                        // คำนวณยอดรวม
                        $model->calculateTotals();
                        $model->save(false);

                        $transaction->commit();

                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PurchaseMaster model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {

                    // บันทึก Master
                    if ($model->save()) {

                        // ลบรายละเอียดเก่า
                        PurchaseDetail::deleteAll(['purchase_master_id' => $model->id]);

                        // บันทึก Details ใหม่
                        $details = Yii::$app->request->post('PurchaseDetail', []);

                        if (!empty($details)) {
                            foreach ($details as $index => $detailData) {
                                if (!empty($detailData['stkcod']) || !empty($detailData['stkdes'])) {
                                    $detail = new PurchaseDetail();
                                    $detail->purchase_master_id = $model->id;
                                    $detail->line_no = $index + 1;
                                    $detail->stkcod = $detailData['stkcod'] ?? null;
                                    $detail->stkdes = $detailData['stkdes'] ?? null;
                                    $detail->uqnty = $detailData['uqnty'] ?? 0;
                                    $detail->unitpr = $detailData['unitpr'] ?? 0;
                                    $detail->disc = $detailData['disc'] ?? null;
                                    $detail->remark = $detailData['remark'] ?? null;

                                    // คำนวณยอดเงิน
                                    $detail->calculateAmount();

                                    if (!$detail->save()) {
                                        throw new Exception('ไม่สามารถบันทึกรายละเอียดสินค้าได้');
                                    }
                                }
                            }
                        }

                        // คำนวณยอดรวม
                        $model->calculateTotals();
                        $model->save(false);

                        $transaction->commit();

                        Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PurchaseMaster model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = $this->findModel($id);

            // ลบรายละเอียด
            PurchaseDetail::deleteAll(['purchase_master_id' => $model->id]);

            // ลบหลัก
            $model->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Export to Excel for EXPRESS
     */
    public function actionExport()
    {
        $date_from = Yii::$app->request->get('date_from');
        $date_to = Yii::$app->request->get('date_to');

        $query = PurchaseMaster::find()
            ->with('purchaseDetails')
            ->orderBy(['docdat' => SORT_ASC, 'docnum' => SORT_ASC]);

        if ($date_from) {
            $query->andWhere(['>=', 'docdat', $date_from]);
        }

        if ($date_to) {
            $query->andWhere(['<=', 'docdat', $date_to]);
        }

        $models = $query->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ตั้งค่า Header
        $headers = [
            'A' => 'DEPCOD',
            'B' => 'DOCNUM',
            'C' => 'DOCDAT',
            'D' => 'SUPCOD',
            'E' => 'SUPNAM',
            'F' => 'STKCOD',
            'G' => 'STKDES',
            'H' => 'UQNTY',
            'I' => 'UNITPR',
            'J' => 'DISC',
            'K' => 'AMOUNT',
            'L' => 'LATE',
        ];

        $row = 3;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
        }

        // ใส่ข้อมูล
        $row = 4;
        foreach ($models as $model) {
            foreach ($model->purchaseDetails as $detail) {
                $sheet->setCellValue('A' . $row, ''); // DEPCOD
                $sheet->setCellValue('B' . $row, $model->docnum);
                $sheet->setCellValue('C' . $row, $model->docdat);
                $sheet->setCellValue('D' . $row, $model->supcod);
                $sheet->setCellValue('E' . $row, $model->supnam);
                $sheet->setCellValue('F' . $row, $detail->stkcod);
                $sheet->setCellValue('G' . $row, $detail->stkdes);
                $sheet->setCellValue('H' . $row, $detail->uqnty);
                $sheet->setCellValue('I' . $row, $detail->unitpr);
                $sheet->setCellValue('J' . $row, $detail->disc);
                $sheet->setCellValue('K' . $row, $detail->amount);
                $sheet->setCellValue('L' . $row, '');

                $row++;
            }
        }

        // ตั้งชื่อไฟล์
        $filename = 'purchase_export_' . date('Ymd_His') . '.xlsx';

        // ส่งออกไฟล์
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Search product by autocomplete
     */
    public function actionSearchProduct($q = '')
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // สมมติว่ามีตาราง product อยู่แล้ว
        // ถ้ายังไม่มี ให้แก้ไขตามชื่อตารางที่มีจริง
        $query = \backend\models\Product::find()
            ->select(['code as value', 'name as label', 'code', 'name', 'cost_price as price'])
            ->where(['like', 'code', $q])
            ->orWhere(['like', 'name', $q])
            ->limit(20);

        $products = $query->asArray()->all();

        return $products;
    }

    /**
     * Finds the PurchaseMaster model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PurchaseMaster the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseMaster::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}