<?php

namespace backend\controllers;

use backend\models\Job;
use backend\models\Quotation;
use common\models\JobLine;
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
use yii\web\UploadedFile;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends Controller
{
    public $enableCsrfValidation = false;

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
            // print_r(Yii::$app->request->post('InvoiceItem', []));return;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // $model->total_amount_text = \backend\models\PurchReq::numtothai($model->total_amount);
                $model->status = Invoice::STATUS_ACTIVE;
                $model->is_billed = 0;
                if ($model->save(false)) {
                    // Handle items
                    $itemsData = Yii::$app->request->post('InvoiceItem', []);
                    //  print_r($itemsData);return;
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
        $items = \backend\models\InvoiceItem::find()->where(['invoice_id' => $id])->all();

        // Ensure at least one item row
        if (empty($items)) {
            $items = [new InvoiceItem()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // $model->total_amount_text = \backend\models\PurchReq::numtothai($model->total_amount);
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
                'product_id' => isset($itemData['product_id']) ? trim($itemData['product_id']) : '',
                'item_description' => isset($itemData['item_description']) ? trim($itemData['item_description']) : '',
                'quantity' => !empty($itemData['quantity']) ? (float)$itemData['quantity'] : 1.000,
                'unit_id' => isset($itemData['unit_id']) ? trim($itemData['unit_id']) : 0,
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
            function ($model) {
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

    public function actionGetJob()
    {
        $id = \Yii::$app->request->post('id');
        $customer_data = null;
        if ($id) {
            $model = Quotation::find()->where(['id' => $id])->one();
            if ($model) {
                $customer_data = \backend\models\Job::findCustomerData($model->id);
            }
        }
        return json_encode($customer_data);
    }
//    public function actionGetJob()
//    {
//        $id = \Yii::$app->request->post('id');
//        $customer_data = null;
//        if ($id) {
//            $model = Job::find()->where(['id' => $id])->one();
//            if ($model) {
//                $customer_data = \backend\models\Job::findCustomerData($model->quotation_id);
//            }
//        }
//        return json_encode($customer_data);
//    }

    public function actionGetJobItems()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $jobId = \Yii::$app->request->post('id');

        if (!$jobId) {
            return [
                'success' => false,
                'message' => 'ไม่พบ ID ของใบเสนอราคา'
            ];
        }

        try {
            // ตรวจสอบว่าใบงานมีอยู่จริง
            $job = \backend\models\Quotation::findOne($jobId);
            if (!$job) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบใบเสนอราคาที่ระบุ'
                ];
            }

            // ดึงรายการสินค้า/บริการจากใบงาน
            // สมมติว่ามีตาราง job_items ที่เก็บรายการสินค้าของแต่ละงาน
            $jobItems = \backend\models\QuotationLine::find()
                ->where(['quotation_id' => $jobId])
                ->orderBy(['id' => SORT_ASC])
                ->all();

            if (empty($jobItems)) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบรายการสินค้า/บริการในใบเสนอราคานี้'
                ];
            }

            // จัดรูปแบบข้อมูลให้ตรงกับที่ฟอร์มต้องการ
            $items = [];
            foreach ($jobItems as $jobItem) {
                $items[] = [
                    'item_description' => $jobItem->product->name,
                    'quantity' => number_format($jobItem->qty, 0),
                    'unit' => $jobItem->product->unit->name ?: 'หน่วย',
                    'unit_price' => $jobItem->line_price,
                    'amount' => number_format($jobItem->qty * $jobItem->line_price, 2),
                    // ข้อมูลเพิ่มเติมที่อาจจำเป็น
                    'product_id' => $jobItem->product_id,
                    'notes' => $jobItem->note,
                ];
            }

            return [
                'success' => true,
                'items' => $items,
                'job_info' => [
                    'job_no' => $job->quotation_no,
                    'job_name' => $job->quotation_no,
                    'customer_name' => 'test',
                ]
            ];

        } catch (\Exception $e) {
            \Yii::error("Error in actionGetJobItems: " . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ];
        }
    }
//    public function actionGetJobItems()
//    {
//        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//
//        $jobId = \Yii::$app->request->post('id');
//
//        if (!$jobId) {
//            return [
//                'success' => false,
//                'message' => 'ไม่พบ ID ของใบงาน'
//            ];
//        }
//
//        try {
//            // ตรวจสอบว่าใบงานมีอยู่จริง
//            $job = \backend\models\Job::findOne($jobId);
//            if (!$job) {
//                return [
//                    'success' => false,
//                    'message' => 'ไม่พบใบงานที่ระบุ'
//                ];
//            }
//
//            // ดึงรายการสินค้า/บริการจากใบงาน
//            // สมมติว่ามีตาราง job_items ที่เก็บรายการสินค้าของแต่ละงาน
//            $jobItems = \common\models\JobLine::find()
//                ->where(['job_id' => $jobId])
//                ->orderBy(['id' => SORT_ASC])
//                ->all();
//
//            if (empty($jobItems)) {
//                return [
//                    'success' => false,
//                    'message' => 'ไม่พบรายการสินค้า/บริการในใบงานนี้'
//                ];
//            }
//
//            // จัดรูปแบบข้อมูลให้ตรงกับที่ฟอร์มต้องการ
//            $items = [];
//            foreach ($jobItems as $jobItem) {
//                $items[] = [
//                    'item_description' => $jobItem->product->name,
//                    'quantity' => number_format($jobItem->qty, 0),
//                    'unit' => $jobItem->product->unit->name ?: 'หน่วย',
//                    'unit_price' => number_format($jobItem->line_price, 2),
//                    'amount' => number_format($jobItem->qty * $jobItem->line_price, 2),
//                    // ข้อมูลเพิ่มเติมที่อาจจำเป็น
//                    'product_id' => $jobItem->product_id,
//                    'notes' => $jobItem->note,
//                ];
//            }
//
//            return [
//                'success' => true,
//                'items' => $items,
//                'job_info' => [
//                    'job_no' => $job->job_no,
//                    'job_name' => $job->job_no,
//                    'customer_name' => 'test',
//                ]
//            ];
//
//        } catch (\Exception $e) {
//            \Yii::error("Error in actionGetJobItems: " . $e->getMessage(), __METHOD__);
//
//            return [
//                'success' => false,
//                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
//            ];
//        }
//    }

    public function actionGetProductInfo()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $request = \Yii::$app->request;

        // ถ้าขอข้อมูลสินค้าทั้งหมดสำหรับ autocomplete
        if ($request->get('action') === 'get-all-products') {
            $products = \backend\models\Product::find()
                ->where(['status' => 1])
                ->all();

            $result = [];
            foreach ($products as $product) {
                $result[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code ?? '',
                    'price' => $product->sale_price ?? 0,
                    'display' => $product->code . ($product->name ? ' (' . $product->name . ')' : '')
                ];
            }

            return $result;
        }

        // ถ้าขอข้อมูลสินค้าเฉพาะ ID (สำหรับการเลือกสินค้า)
        $id = $request->get('id');
        if ($id) {
            $product = \backend\models\Product::findOne($id);
            if ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'name' => $product->name,
                    'code' => $product->code ?? '',
                    'price' => $product->sale_price ?? 0,
                    'display' => $product->code . ($product->name ? ' (' . $product->name . ')' : '')
                ];
            }
        }

        return ['error' => 'Product not found'];
    }

    public function actionAddDocFile()
    {
        $id = \Yii::$app->request->post('id');
        if ($id) {
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "invoice_" . time() . "_" . $loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/invoice_doc/' . $upfiles)) {
                        $model_doc = new \common\models\InvoiceDoc();
                        $model_doc->invoice_id = $id;
                        $model_doc->doc = $upfiles;
                        $model_doc->created_by = \Yii::$app->user->id;
                        $model_doc->created_at = time();
                        $model_doc->save(false);
                    }
                    $loop++;
                }
            }

        }
        return $this->redirect(['update', 'id' => $id]);
    }

    public function actionDeleteDocFile()
    {
        $id = \Yii::$app->request->post('id');
        $doc_delete_list = trim(\Yii::$app->request->post('doc_delete_list'));
        if ($id) {
            $model_doc = \common\models\InvoiceDoc::find()->where(['invoice_id' => $id, 'doc' => $doc_delete_list])->one();
            if ($model_doc) {
                if ($model_doc->delete()) {
                    if (file_exists('uploads/invoice_doc/' . $model_doc->doc)) {
                        unlink('uploads/invoice_doc/' . $model_doc->doc);
                    }
                }
            }
        }
        return $this->redirect(['update', 'id' => $id]);
    }

    public function actionGetPaymentTermDay(){
        $id = \Yii::$app->request->post('id');
        $model = \common\models\PaymentTerm::find()->where(['id' => $id])->one();
        if($model) {
           echo $model->day_count;
        }else{
            echo 0;
        }
    }

}