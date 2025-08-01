<?php

namespace backend\controllers;

use Yii;
use backend\models\DebitNote;
use backend\models\DebitNoteItem;
use backend\models\DebitNoteSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use kartik\mpdf\Pdf;
use yii\web\Response;

/**
 * DebitNoteController implements the CRUD actions for DebitNote model.
 */
class DebitNoteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
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
        ];
    }

    /**
     * Lists all DebitNote models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DebitNoteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DebitNote model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new DebitNote model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DebitNote();
        $model->document_date = date('Y-m-d');
        $model->vat_percent = 7;

        $modelsItem = [new DebitNoteItem];

        if ($model->load(Yii::$app->request->post())) {
            $modelsItem = DebitNoteItem::createMultiple(DebitNoteItem::class);
            DebitNoteItem::loadMultiple($modelsItem, Yii::$app->request->post());

            // Generate document number
            if (empty($model->document_no)) {
                $model->generateDocumentNo();
            }

            // Ajax validation
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::merge(
                    ActiveForm::validateMultiple($modelsItem),
                    ActiveForm::validate($model)
                );
            }

            // Validate all models
            $valid = $model->validate();
            $valid = DebitNoteItem::validateMultiple($modelsItem) && $valid;

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $model->save(false)) {
                        foreach ($modelsItem as $index => $modelItem) {
                            $modelItem->debit_note_id = $model->id;
                            $modelItem->item_no = $index + 1;
                            if (!($flag = $modelItem->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }

                    if ($flag) {
                        // Calculate totals
                        $model->calculateTotals();
                        $model->save(false);

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลสำเร็จ');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelsItem' => (empty($modelsItem)) ? [new DebitNoteItem] : $modelsItem
        ]);
    }

    /**
     * Updates an existing DebitNote model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelsItem = $model->debitNoteItems;

        if ($model->load(Yii::$app->request->post())) {
            $oldItemIDs = ArrayHelper::map($modelsItem, 'id', 'id');
            $modelsItem = DebitNoteItem::createMultiple(DebitNoteItem::class, $modelsItem);
            DebitNoteItem::loadMultiple($modelsItem, Yii::$app->request->post());
            $deletedItemIDs = array_diff($oldItemIDs, array_filter(ArrayHelper::map($modelsItem, 'id', 'id')));

            // Ajax validation
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::merge(
                    ActiveForm::validateMultiple($modelsItem),
                    ActiveForm::validate($model)
                );
            }

            // Validate all models
            $valid = $model->validate();
            $valid = DebitNoteItem::validateMultiple($modelsItem) && $valid;

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $model->save(false)) {
                        if (!empty($deletedItemIDs)) {
                            DebitNoteItem::deleteAll(['id' => $deletedItemIDs]);
                        }
                        foreach ($modelsItem as $index => $modelItem) {
                            $modelItem->debit_note_id = $model->id;
                            $modelItem->item_no = $index + 1;
                            if (!($flag = $modelItem->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }

                    if ($flag) {
                        // Calculate totals
                        $model->calculateTotals();
                        $model->save(false);

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลสำเร็จ');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'modelsItem' => (empty($modelsItem)) ? [new DebitNoteItem] : $modelsItem
        ]);
    }

    /**
     * Deletes an existing DebitNote model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->status == DebitNote::STATUS_APPROVED) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบเอกสารที่อนุมัติแล้วได้');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลสำเร็จ');
        }

        return $this->redirect(['index']);
    }

    /**
     * Approve document
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if ($model->status == DebitNote::STATUS_DRAFT) {
            $model->status = DebitNote::STATUS_APPROVED;
            $model->approved_by = Yii::$app->user->id;
            $model->approved_date = date('Y-m-d H:i:s');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'อนุมัติเอกสารสำเร็จ');
            } else {
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการอนุมัติเอกสาร');
            }
        } else {
            Yii::$app->session->setFlash('warning', 'เอกสารนี้ได้รับการอนุมัติแล้ว');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Cancel document
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if ($model->status != DebitNote::STATUS_CANCELLED) {
            $model->status = DebitNote::STATUS_CANCELLED;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'ยกเลิกเอกสารสำเร็จ');
            } else {
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการยกเลิกเอกสาร');
            }
        } else {
            Yii::$app->session->setFlash('warning', 'เอกสารนี้ถูกยกเลิกแล้ว');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Print document as PDF
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        $content = $this->renderPartial('_print', [
            'model' => $model,
        ]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => '@backend/web/css/print.css',
            'options' => [
                'title' => 'ใบเพิ่มหนี้ ' . $model->document_no,
                'defaultFont' => 'prompt'
            ],
            'methods' => [
                'SetHeader' => [''],
                'SetFooter' => [''],
            ]
        ]);

        return $pdf->render();
    }

    /**
     * Export to Excel
     */
    public function actionExport()
    {
        $searchModel = new DebitNoteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $filename = 'debit_notes_' . date('YmdHis') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['เลขที่เอกสาร', 'วันที่', 'ลูกค้า', 'มูลค่า', 'ภาษี', 'รวมทั้งสิ้น', 'สถานะ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Data
        $row = 2;
        foreach ($dataProvider->models as $model) {
            $sheet->setCellValue('A' . $row, $model->document_no);
            $sheet->setCellValue('B' . $row, Yii::$app->formatter->asDate($model->document_date, 'php:d/m/Y'));
            $sheet->setCellValue('C' . $row, $model->customer->customer_name_th);
            $sheet->setCellValue('D' . $row, $model->adjust_amount);
            $sheet->setCellValue('E' . $row, $model->vat_amount);
            $sheet->setCellValue('F' . $row, $model->total_amount);
            $sheet->setCellValue('G' . $row, $model->getStatusLabel());
            $row++;
        }

        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Finds the DebitNote model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DebitNote the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DebitNote::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}