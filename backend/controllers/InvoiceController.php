<?php

namespace backend\controllers;

use backend\models\Job;
use Yii;
use backend\models\Invoice;
use backend\models\InvoiceItem;
use backend\models\Customer;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends Controller
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
     * Lists all Invoice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Invoice::find()->where(['status' => Invoice::STATUS_ACTIVE])->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Invoice model.
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
     * Invoice type selection page
     * @return mixed
     */
    public function actionSelect()
    {
        return $this->render('select');
    }

    /**
     * Creates a new Invoice model.
     * @param string $type Invoice type
     * @param integer|null $copy_from Copy from existing invoice
     * @return mixed
     */
    public function actionCreate($type = null, $copy_from = null)
    {
        if (!$type) {
            return $this->redirect(['select']);
        }

        $model = new Invoice();
        $model->invoice_type = $type;
        $model->invoice_date = date('Y-m-d');
        $model->vat_percent = 7.00;

        // Initialize with one empty item
        $items = [new InvoiceItem()];

        // Copy from existing invoice
        if ($copy_from) {
            $sourceInvoice = $this->findModel($copy_from);
            $model->attributes = $sourceInvoice->attributes;
            $model->id = null;
            $model->invoice_number = null;
            $model->invoice_type = $type;
            $model->created_at = null;
            $model->updated_at = null;

            $items = [];
            foreach ($sourceInvoice->items as $sourceItem) {
                $item = new InvoiceItem();
                $item->attributes = $sourceItem->attributes;
                $item->id = null;
                $item->invoice_id = null;
                $items[] = $item;
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Handle items
                    $itemsData = Yii::$app->request->post('InvoiceItem', []);
                    $this->saveItems($model, $itemsData);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'items' => $items,
            'customers' => $this->getCustomersList(),
        ]);
    }

    /**
     * Updates an existing Invoice model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $items = $model->items;

        // Ensure at least one item row
        if (empty($items)) {
            $items = [new InvoiceItem()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Delete existing items
                    InvoiceItem::deleteAll(['invoice_id' => $model->id]);

                    // Handle new items
                    $itemsData = Yii::$app->request->post('InvoiceItem', []);
                    $this->saveItems($model, $itemsData);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items,
            'customers' => $this->getCustomersList(),
        ]);
    }

    /**
     * Deletes an existing Invoice model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = Invoice::STATUS_CANCELLED;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'ยกเลิกเอกสารเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * Copy invoice to new type
     * @param integer $id
     * @param string $new_type
     * @return mixed
     */
    public function actionCopy($id, $new_type)
    {
        $sourceModel = $this->findModel($id);

        if (!in_array($new_type, array_keys(Invoice::getTypeOptions()))) {
            throw new NotFoundHttpException('Invalid invoice type.');
        }

        return $this->redirect(['create', 'type' => $new_type, 'copy_from' => $id]);
    }

    /**
     * Print invoice
     * @param integer $id
     * @return mixed
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        // Choose print template based on invoice type
        $template = $this->getPrintTemplate($model->invoice_type);

        return $this->render($template, [
            'model' => $model,
        ]);
    }

    /**
     * Get customer data via AJAX
     * @param string $code
     * @return array
     */
    public function actionGetCustomer($code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $customer = Customer::findOne(['customer_code' => $code, 'status' => Customer::STATUS_ACTIVE]);

        if ($customer) {
            return [
                'success' => true,
                'data' => [
                    'customer_name' => $customer->customer_name,
                    'customer_address' => $customer->customer_address,
                    'customer_tax_id' => $customer->tax_id,
                    'credit_terms' => $customer->credit_terms,
                ]
            ];
        }

        return ['success' => false, 'message' => 'ไม่พบข้อมูลลูกค้า'];
    }

    /**
     * Save items
     */
    private function saveItems($model, $itemsData)
    {
        $sortOrder = 1;
        foreach ($itemsData as $itemData) {
            // Skip empty rows
            if (empty($itemData['item_description']) && empty($itemData['quantity'])) {
                continue;
            }

            $item = new InvoiceItem();
            $item->invoice_id = $model->id;
            $item->sort_order = $sortOrder++;

            // Clean and validate data
            $cleanData = [
                'item_seq' => $sortOrder - 1,
                'item_description' => isset($itemData['item_description']) ? trim($itemData['item_description']) : '',
                'quantity' => !empty($itemData['quantity']) ? (float)$itemData['quantity'] : 1.000,
                'unit' => isset($itemData['unit']) ? trim($itemData['unit']) : 'หน่วย',
                'unit_price' => !empty($itemData['unit_price']) ? (float)$itemData['unit_price'] : 0.00,
            ];

            $item->attributes = $cleanData;

            if (!$item->save()) {
                Yii::error('Failed to save item: ' . json_encode($item->errors), __METHOD__);
            }
        }

        // Update total amounts
        $model->updateAmountsFromItems();
    }

    /**
     * Get customers list for dropdown
     */
    private function getCustomersList()
    {
        return ArrayHelper::map(
            Job::find()->all(),
            'job_no',
            function($model) {
                return $model->job_no;
            }
        );

//        return ArrayHelper::map(
//            Customer::getActiveCustomersList(),
//            'customer_code',
//            function($model) {
//                return $model->customer_code . ' - ' . $model->customer_name;
//            }
//        );
    }

    /**
     * Get print template based on invoice type
     */
    private function getPrintTemplate($type)
    {
        $templates = [
            Invoice::TYPE_QUOTATION => 'print-quotation',
            Invoice::TYPE_BILL_PLACEMENT => 'print-bill-placement',
            Invoice::TYPE_TAX_INVOICE => 'print-tax-invoice',
            Invoice::TYPE_RECEIPT => 'print-receipt',
        ];

        return isset($templates[$type]) ? $templates[$type] : 'print-invoice';
    }

    /**
     * Finds the Invoice model based on its primary key value.
     * @param integer $id
     * @return Invoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}