<?php

namespace backend\controllers;

use backend\models\InvoicePaymentReceipt;
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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends BaseController
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
                    'cancel' => ['POST'],
                    'migrate-billing' => ['POST'],
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
        $searchModel = new \backend\models\InvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
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
        $model = $this->findModel($id);
        $correct_text = \backend\models\PurchReq::numtothai(number_format($model->total_amount, 2, '.', ''));
        if ($model->total_amount_text !== $correct_text) {
            $model->total_amount_text = $correct_text;
            $model->save(false);
        }
        return $this->render('view', [
            'model' => $model,
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
     * @param integer|null $ocr_id Create from OCR temp invoice
     * @return mixed
     */
    public function actionCreate($type = null, $copy_from = null, $ocr_id = null)
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

            // If copying to a different type (e.g. Invoice -> Receipt), 
            // set the quotation_id to the source invoice ID to maintain reference.
            if ($sourceInvoice->invoice_type != $type) {
                 if ($type == Invoice::TYPE_RECEIPT && $sourceInvoice->invoice_type !== Invoice::TYPE_RECEIPT) {
                    $model->quotation_id = $sourceInvoice->id;
                 } else {
                    $model->quotation_id = $sourceInvoice->quotation_id;
                 }
            }
        } elseif ($ocr_id) {
            // Create from OCR TempInvoice
            $tempInvoice = \backend\models\TempInvoice::findOne($ocr_id);
            if ($tempInvoice) {
                $model->customer_name = $tempInvoice->customer_name;
                $model->customer_address = $tempInvoice->customer_address;
                $model->customer_tax_id = $tempInvoice->customer_tax_id;
                
                if ($tempInvoice->invoice_date) {
                    $model->invoice_date = $tempInvoice->invoice_date;
                }
                
                $model->po_number = $tempInvoice->invoice_number; 

                $model->subtotal = $tempInvoice->subtotal;
                $model->vat_amount = $tempInvoice->vat_amount;
                $model->total_amount = $tempInvoice->total_amount;
                
                // Add vendor name to special note if it exists
                $model->special_note = "";
                if ($tempInvoice->vendor_name) {
                    $model->special_note .= "อ้างอิงจากผู้ขาย: " . $tempInvoice->vendor_name;
                }

                $items = [];
                foreach ($tempInvoice->tempInvoiceLines as $line) {
                    $item = new InvoiceItem();
                    $item->item_description = $line->description;
                    $item->quantity = $line->quantity ?: 1;
                    $item->unit = $line->unit;
                    $item->unit_price = $line->unit_price;
                    $item->amount = $line->amount;
                    $items[] = $item;
                }
                if (empty($items)) {
                    $items = [new InvoiceItem()];
                }
            }
        }

        // Simplified form for Receipt from copy
//        if ($type == Invoice::TYPE_RECEIPT && $copy_from) {
//             // Pre-load data for modal if it's a new request (not post)
//             if (!Yii::$app->request->isPost) {
//                 // The model is already populated with copied data above
//             }
//             
//             // If it's a POST request for the modal form
//             if ($model->load(Yii::$app->request->post())) {
//                $transaction = Yii::$app->db->beginTransaction();
//                try {
//                    $model->status = Invoice::STATUS_ACTIVE;
//                    $model->is_billed = 0;
//                    
//                    // Ensure quotation_id is preserved/set correctly
//                    if ($copy_from) {
//                         $sourceInvoice = $this->findModel($copy_from);
//                         if ($sourceInvoice->invoice_type != $type) {
//                             if ($type == Invoice::TYPE_RECEIPT && $sourceInvoice->invoice_type !== Invoice::TYPE_RECEIPT) {
//                                $model->quotation_id = $sourceInvoice->id;
//                             } else {
//                                $model->quotation_id = $sourceInvoice->quotation_id;
//                             }
//                         }
//                    }
//
//                    if ($model->save(false)) {
//                        // Save items (from $items variable populated from source)
//                        foreach ($items as $item) {
//                            $item->invoice_id = $model->id;
//                            if (!$item->save(false)) {
//                                throw new \Exception('Failed to save item');
//                            }
//                        }
//
//                        $model->updateAmountsFromItems();
//
//                        $relation = new \backend\models\InvoiceRelation();
//                        $relation->parent_invoice_id = $copy_from;
//                        $relation->child_invoice_id = $model->id;
//                        $relation->relation_type = $sourceInvoice->invoice_type . '_to_' . $type;
//                        $relation->save(false);
//
//                        $transaction->commit();
//                        Yii::$app->session->setFlash('success', 'สร้างใบเสร็จรับเงินเรียบร้อยแล้ว');
//                        return $this->redirect(['view', 'id' => $model->id]);
//                    }
//                } catch (\Exception $e) {
//                    $transaction->rollBack();
//                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
//                }
//            }
//            
//            return $this->render('create_receipt_modal', [
//                'model' => $model,
//            ]);
//        }

        if ($model->load(Yii::$app->request->post())) {
            // print_r(Yii::$app->request->post('InvoiceItem', []));return;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // $model->total_amount_text = \backend\models\PurchReq::numtothai($model->total_amount);
                $model->is_billed = 0;
                
                // Ensure quotation_id is set if we are copying from another invoice
                // This is crucial because the form might not include quotation_id as a hidden field,
                // or it might be overwritten during load().
                if ($copy_from && empty($model->quotation_id)) {
                     $sourceInvoice = $this->findModel($copy_from);
                     if ($sourceInvoice->invoice_type != $type) {
                         if ($type == Invoice::TYPE_RECEIPT && $sourceInvoice->invoice_type !== Invoice::TYPE_RECEIPT) {
                            $model->quotation_id = $sourceInvoice->id;
                         } else {
                            $model->quotation_id = $sourceInvoice->quotation_id;
                         }
                     }
                }

                if ($model->save(false)) {
                    // Handle items
                    $itemsData = Yii::$app->request->post('InvoiceItem', []);
                    //  print_r($itemsData);return;
                    $this->saveItems($model, $itemsData);

                    // Save selected document relationships if provided (e.g. from receipt create)
                    $selectedDocs = Yii::$app->request->post('selected_related_docs', []);
                    if (!empty($selectedDocs) && is_array($selectedDocs)) {
                        foreach ($selectedDocs as $docInfo) {
                            $parts = explode(':', $docInfo);
                            if (count($parts) === 2) {
                                $dType = $parts[0];
                                $dId = (int)$parts[1];
                                $relType = ($dType === 'credit_note') ? 'credit_note_to_receipt' : (($dType === 'debit_note') ? 'debit_note_to_receipt' : 'tax_invoice_to_receipt');
                                
                                $existingRel = \backend\models\InvoiceRelation::find()
                                    ->where([
                                        'parent_invoice_id' => $dId,
                                        'child_invoice_id' => $model->id,
                                        'relation_type' => $relType
                                    ])->one();
                                
                                if (!$existingRel) {
                                    $rel = new \backend\models\InvoiceRelation();
                                    $rel->parent_invoice_id = $dId;
                                    $rel->child_invoice_id = $model->id;
                                    $rel->relation_type = $relType;
                                    $rel->save(false);
                                }
                            }
                        }
                    }

                    // Save invoice relationship if this is a copy
                    if ($copy_from) {
                        $sourceInvoice = $this->findModel($copy_from); // Need to re-fetch source invoice to get type
                        $relation = new \backend\models\InvoiceRelation();
                        $relation->parent_invoice_id = $copy_from;
                        $relation->child_invoice_id = $model->id;
                        $relation->relation_type = $sourceInvoice->invoice_type . '_to_' . $type;
                        if (!$relation->save()) {
                            Yii::error('Failed to save invoice relation: ' . json_encode($relation->errors), __METHOD__);
                        }

                        // Save Payment History ONLY if the new document is a RECEIPT
                        if ($type == Invoice::TYPE_RECEIPT) {
                            $history = new \backend\models\InvoicePaymentHistory();
                            $history->invoice_id = $copy_from;
                            $history->receipt_id = $model->id;
                            $history->amount = $model->total_amount;
                            $history->payment_date = $model->invoice_date ?: date('Y-m-d');
                            $history->company_id = $model->company_id;
                            if (!$history->save()) {
                                Yii::error('Failed to save payment history: ' . json_encode($history->errors), __METHOD__);
                            }
                        }
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        $sourceInvoice = null;
        $totalPaid = 0;
        if ($copy_from) {
            $sourceInvoice = $this->findModel($copy_from);
            
            // Calculate total paid from existing relations
            $relations = \backend\models\InvoiceRelation::find()
                ->where(['parent_invoice_id' => $copy_from])
                ->all();
            
            foreach ($relations as $rel) {
                if ($rel->childInvoice && $rel->childInvoice->status == Invoice::STATUS_ACTIVE) {
                    $totalPaid += $rel->childInvoice->total_amount;
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'items' => $items,
            'customers' => $this->getCustomersList(),
            'copy_from' => $copy_from,
            'sourceInvoice' => $sourceInvoice,
            'totalPaid' => $totalPaid,
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

                    // Save selected document relationships if provided
                    $selectedDocs = Yii::$app->request->post('selected_related_docs', []);
                    if (!empty($selectedDocs) && is_array($selectedDocs)) {
                        foreach ($selectedDocs as $docInfo) {
                            $parts = explode(':', $docInfo);
                            if (count($parts) === 2) {
                                $dType = $parts[0];
                                $dId = (int)$parts[1];
                                $relType = ($dType === 'credit_note') ? 'credit_note_to_receipt' : (($dType === 'debit_note') ? 'debit_note_to_receipt' : 'tax_invoice_to_receipt');
                                
                                $existingRel = \backend\models\InvoiceRelation::find()
                                    ->where([
                                        'parent_invoice_id' => $dId,
                                        'child_invoice_id' => $model->id,
                                        'relation_type' => $relType
                                    ])->one();
                                
                                if (!$existingRel) {
                                    $rel = new \backend\models\InvoiceRelation();
                                    $rel->parent_invoice_id = $dId;
                                    $rel->child_invoice_id = $model->id;
                                    $rel->relation_type = $relType;
                                    $rel->save(false);
                                }
                            }
                        }
                    }

                    // Update Payment History if exists
                    $history = \backend\models\InvoicePaymentHistory::findOne(['receipt_id' => $model->id]);
                    if ($history) {
                        $history->amount = $model->total_amount;
                        $history->payment_date = $model->invoice_date;
                        $history->save();
                    }

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
    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->status = Invoice::STATUS_CANCELLED;
        $model->save(false);

        \backend\models\ActionLogModel::logModelAction('cancel', $model, ['message' => 'ยกเลิกเอกสาร: ' . $model->invoice_number]);

        Yii::$app->session->setFlash('success', 'ยกเลิกเอกสารเรียบร้อยแล้ว');
        return $this->redirect(['index']);
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
        \backend\models\ActionLogModel::logModelAction('delete', $model, ['message' => 'ลบเอกสาร: ' . $model->invoice_number]);

        Invoice::deleteAll(['id' => $id]);
        // Optional: Delete related items if not handled by foreign key constraints
        InvoiceItem::deleteAll(['invoice_id' => $id]);

        Yii::$app->session->setFlash('success', 'ลบเอกสารเรียบร้อยแล้ว');
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
            // ใช้ AddressHelper จัดรูปแบบที่อยู่ (แยกกรุงเทพฯ กับจังหวัดอื่นอัตโนมัติ)
            $formattedAddress = \backend\helpers\AddressHelper::formatCustomerAddress($customer);

            return [
                'success' => true,
                'data' => [
                    'customer_name' => $customer->customer_name,
                    'customer_address' => $formattedAddress,
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

            // Get unit name from unit_id if not provided
            $unitName = 'หน่วย'; // default value
            if (!empty($itemData['unit_id'])) {
                $unit = \backend\models\Unit::findOne($itemData['unit_id']);
                if ($unit) {
                    $unitName = $unit->name;
                }
            } elseif (isset($itemData['unit']) && !empty(trim($itemData['unit']))) {
                $unitName = trim($itemData['unit']);
            }

            // Clean and validate data
            $cleanData = [
                'item_seq' => $sortOrder - 1,
                'product_id' => isset($itemData['product_id']) ? trim($itemData['product_id']) : '',
                'item_description' => isset($itemData['item_description']) ? $this->cleanProductDescription($itemData['item_description']) : '',
                'quantity' => !empty($itemData['quantity']) ? (float)$itemData['quantity'] : 1.000,
                'unit_id' => isset($itemData['unit_id']) && !empty($itemData['unit_id']) ? (int)$itemData['unit_id'] : 0,
                'unit' => $unitName,
                'unit_price' => !empty($itemData['unit_price']) ? (float)$itemData['unit_price'] : 0.00,
                'has_vat' => isset($itemData['has_vat']) ? (int)$itemData['has_vat'] : 1,
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
     * Clean product description by removing codes and common prefixes
     */
    private function cleanProductDescription($description)
    {
        return \backend\helpers\ProductHelper::cleanDescription($description);
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

    /**
     * ดึงเอกสารใบแจ้งหนี้/ใบกำกับภาษี และใบลดหนี้/ใบเพิ่มหนี้ ที่ผูกกับ Quotation
     * สำหรับนำมาเลือกทำใบเสร็จรับเงิน (receipt)
     */
    public function actionGetRelatedDocs()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $quotationId = \Yii::$app->request->post('quotation_id') ?: \Yii::$app->request->get('quotation_id');
        $currentInvoiceId = \Yii::$app->request->post('current_invoice_id') ?: \Yii::$app->request->get('current_invoice_id');

        if (!$quotationId) {
            return ['success' => false, 'message' => 'ไม่พบรหัสใบเสนอราคา'];
        }

        try {
            // 1. ดึงใบแจ้งหนี้ / ใบกำกับภาษี / ใบวางบิล ที่ผูกกับ quotation_id
            $invoices = Invoice::find()
                ->where(['quotation_id' => $quotationId])
                ->andWhere(['!=', 'invoice_type', Invoice::TYPE_RECEIPT])
                ->andWhere(['status' => Invoice::STATUS_ACTIVE])
                ->orderBy(['invoice_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            $invIds = [];
            $invoiceList = [];
            foreach ($invoices as $inv) {
                $invIds[] = $inv->id;

                // ตรวจสอบว่าใบแจ้งหนี้/ใบกำกับนี้เคยถูกนำไปทำใบเสร็จแล้วหรือไม่
                $receiptRelQuery = \backend\models\InvoiceRelation::find()
                    ->alias('ir')
                    ->innerJoin('invoices r', 'ir.child_invoice_id = r.id')
                    ->where([
                        'ir.parent_invoice_id' => $inv->id,
                        'r.invoice_type' => Invoice::TYPE_RECEIPT,
                        'r.status' => Invoice::STATUS_ACTIVE,
                    ]);
                if ($currentInvoiceId) {
                    $receiptRelQuery->andWhere(['!=', 'r.id', $currentInvoiceId]);
                }
                $existingReceiptRel = $receiptRelQuery->one();

                // ตรวจสอบกรณีที่สร้างใบเสร็จโดยมี quotation_id ชี้ไปที่ ID ของใบแจ้งหนี้โดยตรง
                $directReceiptQuery = Invoice::find()
                    ->where([
                        'quotation_id' => $inv->id,
                        'invoice_type' => Invoice::TYPE_RECEIPT,
                        'status' => Invoice::STATUS_ACTIVE,
                    ]);
                if ($currentInvoiceId) {
                    $directReceiptQuery->andWhere(['!=', 'id', $currentInvoiceId]);
                }
                $existingDirectReceipt = $directReceiptQuery->one();

                $usedReceipt = null;
                if ($existingReceiptRel) {
                    $usedReceipt = Invoice::findOne($existingReceiptRel->child_invoice_id);
                } elseif ($existingDirectReceipt) {
                    $usedReceipt = $existingDirectReceipt;
                }

                $typeLabel = $inv->getTypeLabel();

                $invoiceList[] = [
                    'id' => $inv->id,
                    'doc_type' => 'invoice',
                    'invoice_type' => $inv->invoice_type,
                    'type_label' => $typeLabel,
                    'doc_no' => $inv->invoice_number,
                    'doc_date' => $inv->invoice_date,
                    'amount' => (float)$inv->total_amount,
                    'vat_amount' => (float)$inv->vat_amount,
                    'is_used' => $usedReceipt ? true : false,
                    'used_by_receipt' => $usedReceipt ? $usedReceipt->invoice_number : null,
                    'used_receipt_id' => $usedReceipt ? $usedReceipt->id : null,
                ];
            }

            // 2. ดึงใบลดหนี้ (Credit Note) ที่ผูกกับ quotation_id หรือ invoice_id ของใบแจ้งหนี้เหล่านี้
            $cnQuery = \backend\models\CreditNote::find()
                ->where(['!=', 'status', 'canceled']);

            $cnConditions = [['quotation_id' => $quotationId]];
            if (!empty($invIds)) {
                $cnConditions[] = ['in', 'invoice_id', $invIds];
            }
            $cnQuery->andWhere(array_merge(['or'], $cnConditions));
            $creditNotes = $cnQuery->orderBy(['document_date' => SORT_ASC, 'id' => SORT_ASC])->all();

            $cnList = [];
            foreach ($creditNotes as $cn) {
                // ตรวจสอบว่าใบลดหนี้นี้เคยถูกนำไปทำใบเสร็จแล้วหรือไม่ (ดูจาก invoice_relations หรือ invoice_items)
                $cnRelQuery = \backend\models\InvoiceRelation::find()
                    ->alias('ir')
                    ->innerJoin('invoices r', 'ir.child_invoice_id = r.id')
                    ->where([
                        'ir.parent_invoice_id' => $cn->id,
                        'ir.relation_type' => 'credit_note_to_receipt',
                        'r.invoice_type' => Invoice::TYPE_RECEIPT,
                        'r.status' => Invoice::STATUS_ACTIVE,
                    ]);
                if ($currentInvoiceId) {
                    $cnRelQuery->andWhere(['!=', 'r.id', $currentInvoiceId]);
                }
                $existingCnRel = $cnRelQuery->one();

                $usedReceipt = null;
                if ($existingCnRel) {
                    $usedReceipt = Invoice::findOne($existingCnRel->child_invoice_id);
                } else {
                    // ตรวจสอบว่ามี item ในใบเสร็จอื่นที่มีการอ้างอิงเลขที่ใบลดหนี้นี้หรือไม่
                    $itemQuery = \backend\models\InvoiceItem::find()
                        ->alias('ii')
                        ->innerJoin('invoices r', 'ii.invoice_id = r.id')
                        ->where([
                            'r.invoice_type' => Invoice::TYPE_RECEIPT,
                            'r.status' => Invoice::STATUS_ACTIVE,
                        ])
                        ->andWhere(['like', 'ii.item_description', $cn->document_no]);
                    if ($currentInvoiceId) {
                        $itemQuery->andWhere(['!=', 'r.id', $currentInvoiceId]);
                    }
                    $existingItem = $itemQuery->one();
                    if ($existingItem) {
                        $usedReceipt = Invoice::findOne($existingItem->invoice_id);
                    }
                }

                $cnList[] = [
                    'id' => $cn->id,
                    'doc_type' => 'credit_note',
                    'type_label' => 'ใบลดหนี้ (Credit Note)',
                    'doc_no' => $cn->document_no,
                    'doc_date' => $cn->document_date,
                    'amount' => (float)$cn->total_amount,
                    'adjust_amount' => (float)$cn->adjust_amount,
                    'vat_amount' => (float)$cn->vat_amount,
                    'reason' => $cn->reason,
                    'is_used' => $usedReceipt ? true : false,
                    'used_by_receipt' => $usedReceipt ? $usedReceipt->invoice_number : null,
                    'used_receipt_id' => $usedReceipt ? $usedReceipt->id : null,
                ];
            }

            // 3. ดึงใบเพิ่มหนี้ (Debit Note) ที่ผูกกับ quotation_id หรือ invoice_id ของใบแจ้งหนี้เหล่านี้
            $dnQuery = \backend\models\DebitNote::find()
                ->where(['!=', 'status', 'canceled']);

            $dnConditions = [['quotation_id' => $quotationId]];
            if (!empty($invIds)) {
                $dnConditions[] = ['in', 'invoice_id', $invIds];
            }
            $dnQuery->andWhere(array_merge(['or'], $dnConditions));
            $debitNotes = $dnQuery->orderBy(['document_date' => SORT_ASC, 'id' => SORT_ASC])->all();

            $dnList = [];
            foreach ($debitNotes as $dn) {
                // ตรวจสอบว่าใบเพิ่มหนี้นี้เคยถูกนำไปทำใบเสร็จแล้วหรือไม่
                $dnRelQuery = \backend\models\InvoiceRelation::find()
                    ->alias('ir')
                    ->innerJoin('invoices r', 'ir.child_invoice_id = r.id')
                    ->where([
                        'ir.parent_invoice_id' => $dn->id,
                        'ir.relation_type' => 'debit_note_to_receipt',
                        'r.invoice_type' => Invoice::TYPE_RECEIPT,
                        'r.status' => Invoice::STATUS_ACTIVE,
                    ]);
                if ($currentInvoiceId) {
                    $dnRelQuery->andWhere(['!=', 'r.id', $currentInvoiceId]);
                }
                $existingDnRel = $dnRelQuery->one();

                $usedReceipt = null;
                if ($existingDnRel) {
                    $usedReceipt = Invoice::findOne($existingDnRel->child_invoice_id);
                } else {
                    $itemQuery = \backend\models\InvoiceItem::find()
                        ->alias('ii')
                        ->innerJoin('invoices r', 'ii.invoice_id = r.id')
                        ->where([
                            'r.invoice_type' => Invoice::TYPE_RECEIPT,
                            'r.status' => Invoice::STATUS_ACTIVE,
                        ])
                        ->andWhere(['like', 'ii.item_description', $dn->document_no]);
                    if ($currentInvoiceId) {
                        $itemQuery->andWhere(['!=', 'r.id', $currentInvoiceId]);
                    }
                    $existingItem = $itemQuery->one();
                    if ($existingItem) {
                        $usedReceipt = Invoice::findOne($existingItem->invoice_id);
                    }
                }

                $dnList[] = [
                    'id' => $dn->id,
                    'doc_type' => 'debit_note',
                    'type_label' => 'ใบเพิ่มหนี้ (Debit Note)',
                    'doc_no' => $dn->document_no,
                    'doc_date' => $dn->document_date,
                    'amount' => (float)$dn->total_amount,
                    'adjust_amount' => (float)$dn->adjust_amount,
                    'vat_amount' => (float)$dn->vat_amount,
                    'reason' => $dn->reason,
                    'is_used' => $usedReceipt ? true : false,
                    'used_by_receipt' => $usedReceipt ? $usedReceipt->invoice_number : null,
                    'used_receipt_id' => $usedReceipt ? $usedReceipt->id : null,
                ];
            }

            return [
                'success' => true,
                'invoices' => $invoiceList,
                'credit_notes' => $cnList,
                'debit_notes' => $dnList,
            ];
        } catch (\Exception $e) {
            \Yii::error("Error in actionGetRelatedDocs: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงเอกสารที่เกี่ยวข้อง: ' . $e->getMessage()
            ];
        }
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
            // ตรวจสอบว่าใบเสนอราคามีอยู่จริง
            $job = \backend\models\Quotation::findOne($jobId);
            if (!$job) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบใบเสนอราคาที่ระบุ'
                ];
            }

            // ดึงรายการสินค้า/บริการจากใบเสนอราคา
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

            $invoiceType = \Yii::$app->request->post('invoice_type', Invoice::TYPE_TAX_INVOICE);
            $currentInvoiceId = \Yii::$app->request->post('current_invoice_id');

            // Get already invoiced items from other active invoices of the same type for this quotation
            $query = \backend\models\InvoiceItem::find()
                ->alias('ii')
                ->innerJoin('invoices i', 'ii.invoice_id = i.id')
                ->where([
                    'i.quotation_id' => $jobId,
                    'i.invoice_type' => $invoiceType,
                    'i.status' => Invoice::STATUS_ACTIVE
                ]);
            
            if ($currentInvoiceId) {
                $query->andWhere(['!=', 'i.id', $currentInvoiceId]);
            }
            
            $alreadyInvoicedItems = $query->all();

            // Create a pool of already invoiced items to match against
            $invoicedPool = [];
            foreach ($alreadyInvoicedItems as $invItem) {
                $normalizedDesc = trim(preg_replace('/\s+/', ' ', $invItem->item_description));
                $key = ($invItem->product_id ?: 0) . '|' . $normalizedDesc . '|' . (float)$invItem->quantity . '|' . (float)$invItem->unit_price;
                $invoicedPool[] = $key;
            }

            // จัดรูปแบบข้อมูลให้ตรงกับที่ฟอร์มต้องการ
            $items = [];
            foreach ($jobItems as $jobItem) {
                $description = $this->cleanProductDescription($jobItem->product_name);
                if (!empty($jobItem->note)) {
                    $description .= " " . $jobItem->note;
                }

                // Try to see if this item has already been invoiced
                $normalizedJobDesc = trim(preg_replace('/\s+/', ' ', $description));
                $itemKey = ($jobItem->product_id ?: 0) . '|' . $normalizedJobDesc . '|' . (float)$jobItem->qty . '|' . (float)$jobItem->line_price;
                
                $poolIndex = array_search($itemKey, $invoicedPool);
                if ($poolIndex !== false) {
                    // This item was already invoiced, remove from pool and skip
                    array_splice($invoicedPool, $poolIndex, 1);
                    continue;
                }

                $unitName = 'หน่วย';
                $unitId = null;
                
                if ($jobItem->product) {
                    $unitId = $jobItem->product->unit_id;
                    if ($jobItem->product->unit) {
                        $unitName = $jobItem->product->unit->name;
                    }
                }

                $items[] = [
                    'item_description' => $description,
                    'quantity' => $jobItem->qty,
                    'unit' => $unitName,
                    'unit_id' => $unitId,
                    'unit_price' => $jobItem->line_price,
                    'amount' => ($jobItem->qty * $jobItem->line_price),
                    'product_id' => $jobItem->product_id,
                    'notes' => $jobItem->note,
                    'has_vat' => 1,
                ];
            }

            return [
                'success' => true,
                'items' => $items,
                'job_info' => [
                    'job_no' => $job->quotation_no,
                    'job_name' => $job->quotation_no,
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

    public function actionGetPaymentTermDay()
    {
        $id = \Yii::$app->request->post('id');
        $model = \common\models\PaymentTerm::find()->where(['id' => $id])->one();
        if ($model) {
            echo $model->day_count;
        } else {
            echo 0;
        }
    }

    public function actionPayment($id)
    {
        $invoice = Invoice::findOne($id);
        if (!$invoice || $invoice->invoice_type !== Invoice::TYPE_RECEIPT) {
            throw new \yii\web\NotFoundHttpException('ไม่พบข้อมูลใบเสร็จรับเงิน');
        }

        $model = new InvoicePaymentReceipt();
        $model->invoice_id = $id;

        if ($this->request->isPost && $model->load($this->request->post())) {
            $extrasOptionIds = Yii::$app->request->post('extras_option_id', []);
            $extrasAmounts = Yii::$app->request->post('extras_amount', []);
            
            $hasExtras = false;
            foreach ($extrasOptionIds as $oid) {
                if ($oid) {
                    $hasExtras = true;
                    break;
                }
            }

            // Validation: Must have at least one part
            if ($model->amount == 0 && !$hasExtras) {
                \Yii::$app->session->setFlash('error', 'กรุณาระบุยอดเงิน หรือเลือกรายการเพิ่มเติมอย่างน้อย 1 รายการ');
                return $this->redirect(['payment', 'id' => $id]);
            }

            // Default payment method for adjustment-only entries
            if ($model->amount == 0 && $hasExtras && empty($model->payment_method)) {
                $model->payment_method = 'รายการปรับปรุงยอด';
            }

            $model->file = \yii\web\UploadedFile::getInstance($model, 'file');
            $model->company_id = \Yii::$app->session->get('company_id');

            if ($model->save()) {
                // Save extra items
                foreach ($extrasOptionIds as $index => $optionId) {
                    if ($optionId && isset($extrasAmounts[$index]) && $extrasAmounts[$index] != 0) {
                        $extra = new \backend\models\InvoicePaymentExtra();
                        $extra->payment_receipt_id = $model->id;
                        $extra->extra_option_id = $optionId;
                        $extra->amount = $extrasAmounts[$index];
                        $extra->save(false);
                    }
                }

                if ($model->file) {
                    $fileName = 'pay_' . $model->id . '_' . time() . '.' . $model->file->extension;
                    $path = 'uploads/payments/';
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    if ($model->file->saveAs($path . $fileName)) {
                        $model->attachment = $path . $fileName;
                        $model->save(false);
                    }
                }
                \Yii::$app->session->setFlash('success', 'บันทึกข้อมูลการรับเงินเรียบร้อยแล้ว');
                return $this->redirect(['payment', 'id' => $id]);
            }
        }

        // ดึงรายการประวัติการรับเงินของ Invoice นี้
        $history = InvoicePaymentReceipt::find()->where(['invoice_id' => $id])->orderBy(['id' => SORT_DESC])->all();

        return $this->render('payment', [
            'model' => $model,
            'invoice' => $invoice,
            'history' => $history,
        ]);
    }

    public function actionDeletePayment($id)
    {
        $model = InvoicePaymentReceipt::findOne($id);
        if ($model) {
            $invoice_id = $model->invoice_id;
            if ($model->attachment && file_exists($model->attachment)) {
                unlink($model->attachment);
            }
            $model->delete();
            \Yii::$app->session->setFlash('success', 'ลบรายการรับเงินเรียบร้อยแล้ว');
            return $this->redirect(['payment', 'id' => $invoice_id]);
        }
        return $this->redirect(['index']);
    }

    public function actionMigrateBilling()
    {
        $billingInvoices = \backend\models\BillingInvoice::find()->all();
        $count = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($billingInvoices as $billing) {
                // Check if already migrated
                $exists = Invoice::find()->where(['invoice_number' => $billing->billing_number, 'invoice_type' => Invoice::TYPE_BILL_PLACEMENT])->exists();
                if ($exists) {
                    continue;
                }

                $customer = \backend\models\Customer::findOne($billing->customer_id);

                $newInvoice = new Invoice();
                $newInvoice->invoice_type = Invoice::TYPE_BILL_PLACEMENT;
                $newInvoice->invoice_number = $billing->billing_number;
                $newInvoice->invoice_date = $billing->billing_date;
                $newInvoice->customer_id = $billing->customer_id;

                if ($customer) {
                    $newInvoice->customer_code = $customer->code;
                    $newInvoice->customer_name = $customer->name;
                    $newInvoice->customer_address = \backend\models\Customer::findFullAddress($customer->id);
                    $newInvoice->customer_tax_id = $customer->taxid;
                }

                $newInvoice->credit_terms = (string)$billing->credit_terms;
                $newInvoice->due_date = $billing->payment_due_date;
                $newInvoice->payment_due_date = $billing->payment_due_date;

                $newInvoice->subtotal = $billing->subtotal;
                $newInvoice->discount_percent = $billing->discount_percent;
                $newInvoice->discount_amount = $billing->discount_amount;
                $newInvoice->vat_percent = $billing->vat_percent;
                $newInvoice->vat_amount = $billing->vat_amount;
                $newInvoice->total_amount = $billing->total_amount;

                $newInvoice->notes = $billing->notes;
                $newInvoice->special_note = $billing->special_note;

                // Map status
                if ($billing->status === 'cancelled') {
                    $newInvoice->status = Invoice::STATUS_CANCELLED;
                } else {
                    $newInvoice->status = Invoice::STATUS_ACTIVE;
                }

                $newInvoice->created_by = $billing->created_by;
                $newInvoice->updated_by = $billing->updated_by;
                $newInvoice->created_at = $billing->created_at;
                $newInvoice->updated_at = $billing->updated_at;
                
                if ($newInvoice->hasAttribute('company_id')) {
                    $newInvoice->company_id = $billing->hasAttribute('company_id') ? $billing->company_id : \Yii::$app->session->get('company_id');
                }

                if ($newInvoice->save(false)) {
                    $items = \backend\models\BillingInvoiceItem::find()->where(['billing_invoice_id' => $billing->id])->all();
                    foreach ($items as $item) {
                        $billedInvoice = Invoice::findOne($item->invoice_id);

                        $newItem = new InvoiceItem();
                        $newItem->invoice_id = $newInvoice->id;
                        $newItem->item_seq = $item->item_seq;
                        if ($billedInvoice) {
                            $newItem->item_description = "ใบแจ้งหนี้เลขที่ " . $billedInvoice->invoice_number . " (วันที่ " . date('d/m/Y', strtotime($billedInvoice->invoice_date)) . ")";
                        } else {
                            $newItem->item_description = "ใบแจ้งหนี้ (ID: " . $item->invoice_id . ")";
                        }
                        $newItem->quantity = 1;
                        $newItem->unit = 'รายการ';
                        $newItem->unit_price = $item->amount;
                        $newItem->amount = $item->amount;
                        $newItem->sort_order = $item->sort_order;
                        $newItem->save(false);

                        // Create relation
                        $relation = new \backend\models\InvoiceRelation();
                        $relation->parent_invoice_id = $newInvoice->id;
                        $relation->child_invoice_id = $item->invoice_id;
                        $relation->relation_type = 'bill_placement_to_invoice';
                        $relation->save(false);
                    }
                    $count++;
                }
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', "ย้ายข้อมูลเรียบร้อยแล้ว $count รายการ");
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', "เกิดข้อผิดพลาด: " . $e->getMessage());
        }
        return $this->redirect(['index']);
    }

    /**
     * ดึงข้อมูลสถานะลูกหนี้ตาม 3 กลุ่มเงื่อนไข
     * 1. ใบแจ้งหนี้ ที่ยังไม่ออกใบกำกับ
     * 2. ใบกำกับที่ออกใบวางบิลแล้ว แต่ยังไม่ออกใบเสร็จ
     * 3. ใบเสร็จที่ออกแล้ว แต่ยังไม่บันทึกรับเงิน
     *
     * @param int|null $category 1, 2, 3 หรือ null (ทั้งหมด)
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $searchKeyword
     * @return array
     */
    protected function getDebtorReportData($category = null, $startDate = null, $endDate = null, $searchKeyword = null)
    {
        $companyId = Yii::$app->session->get('company_id');

        $baseSelect = "
            i.id,
            i.invoice_number,
            i.invoice_date,
            i.due_date,
            i.payment_due_date,
            i.payment_term_id,
            pt.day_count as term_days,
            i.customer_name,
            COALESCE(j.job_no, j2.job_no, q.quotation_no, i.po_number, '-') as job_no,
            i.total_amount,
            COALESCE(cn.total_cn, 0) as total_cn,
            COALESCE(dn.total_dn, 0) as total_dn,
            COALESCE(paid.total_paid, 0) as total_paid
        ";

        $baseJoins = "
            LEFT JOIN payment_term pt ON i.payment_term_id = pt.id
            LEFT JOIN job j ON i.job_id = j.id
            LEFT JOIN quotation q ON i.quotation_id = q.id
            LEFT JOIN job j2 ON q.id = j2.quotation_id
            LEFT JOIN (
                SELECT invoice_id, SUM(total_amount) as total_cn 
                FROM credit_note 
                WHERE status = 1 
                GROUP BY invoice_id
            ) cn ON cn.invoice_id = i.id
            LEFT JOIN (
                SELECT invoice_id, SUM(total_amount) as total_dn 
                FROM debit_note 
                WHERE status = 1 
                GROUP BY invoice_id
            ) dn ON dn.invoice_id = i.id
            LEFT JOIN (
                SELECT 
                    pr.invoice_id,
                    SUM(COALESCE(pr.amount, 0) + COALESCE(ext.extra_amount, 0)) as total_paid
                FROM invoice_payment_receipt pr
                LEFT JOIN (
                    SELECT payment_receipt_id, SUM(amount) as extra_amount 
                    FROM invoice_payment_extra 
                    GROUP BY payment_receipt_id
                ) ext ON ext.payment_receipt_id = pr.id
                GROUP BY pr.invoice_id
            ) paid ON paid.invoice_id = i.id
        ";

        $companyCondition = $companyId ? " AND (i.company_id = " . intval($companyId) . " OR i.company_id IS NULL)" : "";
        $dateCondition = "";
        if (!empty($startDate) && !empty($endDate)) {
            $dateCondition = " AND (i.invoice_date BETWEEN '" . addslashes($startDate) . "' AND '" . addslashes($endDate) . "')";
        } elseif (!empty($startDate)) {
            $dateCondition = " AND (i.invoice_date >= '" . addslashes($startDate) . "')";
        } elseif (!empty($endDate)) {
            $dateCondition = " AND (i.invoice_date <= '" . addslashes($endDate) . "')";
        }

        $searchCondition = "";
        if (!empty($searchKeyword)) {
            $kw = addslashes($searchKeyword);
            $searchCondition = " AND (i.invoice_number LIKE '%$kw%' OR i.customer_name LIKE '%$kw%' OR j.job_no LIKE '%$kw%' OR j2.job_no LIKE '%$kw%' OR q.quotation_no LIKE '%$kw%' OR i.po_number LIKE '%$kw%')";
        }

        $results = [
            1 => [],
            2 => [],
            3 => [],
        ];

        // 1. ใบแจ้งหนี้ ที่ยังไม่ออกใบกำกับ
        if (empty($category) || $category == 1) {
            $sql1 = "
                SELECT $baseSelect
                FROM invoices i
                $baseJoins
                LEFT JOIN invoice_relations ir ON ir.parent_invoice_id = i.id AND ir.relation_type = 'quotation_to_tax_invoice'
                WHERE i.invoice_type = 'quotation'
                  AND (i.status IS NULL OR i.status != 0)
                  AND ir.id IS NULL
                  $companyCondition
                  $dateCondition
                  $searchCondition
                ORDER BY i.invoice_date DESC, i.id DESC
            ";
            $results[1] = Yii::$app->db->createCommand($sql1)->queryAll();
        }

        // 2. ใบกำกับที่ออกใบวางบิลแล้ว แต่ยังไม่ออกใบเสร็จ
        if (empty($category) || $category == 2) {
            $sql2 = "
                SELECT $baseSelect
                FROM invoices i
                $baseJoins
                INNER JOIN invoice_relations ir_bill ON ir_bill.parent_invoice_id = i.id AND ir_bill.relation_type = 'tax_invoice_to_bill_placement'
                LEFT JOIN invoice_relations ir_rcpt ON ir_rcpt.parent_invoice_id = i.id AND ir_rcpt.relation_type = 'tax_invoice_to_receipt'
                LEFT JOIN invoice_relations ir_bill_rcpt ON ir_bill_rcpt.parent_invoice_id = ir_bill.child_invoice_id AND ir_bill_rcpt.relation_type = 'bill_placement_to_receipt'
                WHERE i.invoice_type = 'tax_invoice'
                  AND (i.status IS NULL OR i.status != 0)
                  AND ir_rcpt.id IS NULL
                  AND ir_bill_rcpt.id IS NULL
                  $companyCondition
                  $dateCondition
                  $searchCondition
                ORDER BY i.invoice_date DESC, i.id DESC
            ";
            $results[2] = Yii::$app->db->createCommand($sql2)->queryAll();
        }

        // 3. ใบเสร็จที่ออกแล้ว แต่ยังไม่บันทึกรับเงิน
        if (empty($category) || $category == 3) {
            $sql3 = "
                SELECT $baseSelect
                FROM invoices i
                $baseJoins
                WHERE i.invoice_type = 'receipt'
                  AND (i.status IS NULL OR i.status != 0)
                  AND ((i.total_amount + COALESCE(dn.total_dn, 0) - COALESCE(cn.total_cn, 0)) - COALESCE(paid.total_paid, 0)) > 0.01
                  $companyCondition
                  $dateCondition
                  $searchCondition
                ORDER BY i.invoice_date DESC, i.id DESC
            ";
            $results[3] = Yii::$app->db->createCommand($sql3)->queryAll();
        }

        // ประมวลผลคำนวณ Due Date, Remaining, Overdue Days ให้พร้อมใช้งาน
        $processRows = function(&$rows, $catName) {
            $today = new \DateTime(date('Y-m-d'));
            foreach ($rows as &$row) {
                $row['category_name'] = $catName;

                // คำนวณวันครบกำหนดชำระ
                $dueDateStr = null;
                if (!empty($row['due_date']) && $row['due_date'] != '0000-00-00') {
                    $dueDateStr = $row['due_date'];
                } elseif (!empty($row['payment_due_date']) && $row['payment_due_date'] != '0000-00-00') {
                    $dueDateStr = $row['payment_due_date'];
                } elseif (!empty($row['term_days']) && intval($row['term_days']) > 0 && !empty($row['invoice_date'])) {
                    $dueDateStr = date('Y-m-d', strtotime($row['invoice_date'] . ' +' . intval($row['term_days']) . ' days'));
                } elseif (!empty($row['invoice_date'])) {
                    $dueDateStr = $row['invoice_date'];
                }
                $row['computed_due_date'] = $dueDateStr;

                // คำนวณวันที่เกินกำหนดชำระ
                $overdueDays = 0;
                if (!empty($dueDateStr)) {
                    $dueDateObj = new \DateTime($dueDateStr);
                    if ($today > $dueDateObj) {
                        $interval = $today->diff($dueDateObj);
                        $overdueDays = (int)$interval->format('%a');
                    }
                }
                $row['overdue_days'] = $overdueDays;

                // ยอดลดหนี้/เพิ่มหนี้สุทธิ
                $cn = floatval($row['total_cn'] ?? 0);
                $dn = floatval($row['total_dn'] ?? 0);
                $row['net_cn_dn'] = $dn - $cn; // ถ้าเพิ่มหนี้เป็นบวก ถ้าลดหนี้เป็นลบ
                $row['cn_amount'] = $cn;
                $row['dn_amount'] = $dn;

                // ยอดค้างชำระ
                $total = floatval($row['total_amount'] ?? 0);
                $paid = floatval($row['total_paid'] ?? 0);
                $remaining = ($total + $dn - $cn) - $paid;
                if ($remaining < 0) {
                    $remaining = 0;
                }
                $row['remaining_amount'] = $remaining;
            }
        };

        $processRows($results[1], '1. ใบแจ้งหนี้ ที่ยังไม่ออกใบกำกับ');
        $processRows($results[2], '2. ใบกำกับที่ออกใบวางบิลแล้ว แต่ยังไม่ออกใบเสร็จ');
        $processRows($results[3], '3. ใบเสร็จที่ออกแล้ว แต่ยังไม่บันทึกรับเงิน');

        return $results;
    }

    /**
     * หน้ารายงานสถานะลูกหนี้
     * @return mixed
     */
    public function actionDebtorReport()
    {
        $category = Yii::$app->request->get('category');
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $searchKeyword = Yii::$app->request->get('search');

        $reportData = $this->getDebtorReportData($category, $startDate, $endDate, $searchKeyword);

        return $this->render('debtor_report', [
            'reportData' => $reportData,
            'category' => $category,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'searchKeyword' => $searchKeyword,
        ]);
    }

    /**
     * Export รายงานสถานะลูกหนี้เป็น Excel (.xlsx)
     */
    public function actionExportDebtor()
    {
        $category = Yii::$app->request->get('category');
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $searchKeyword = Yii::$app->request->get('search');

        $reportData = $this->getDebtorReportData($category, $startDate, $endDate, $searchKeyword);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('MCO System')
            ->setLastModifiedBy('MCO System')
            ->setTitle('รายงานสถานะลูกหนี้')
            ->setSubject('รายงานสถานะลูกหนี้');

        $categoryTitles = [
            1 => '1. ใบแจ้งหนี้ ที่ยังไม่ออกใบกำกับ',
            2 => '2. ใบกำกับที่ออกใบวางบิลแล้ว แต่ยังไม่ออกใบเสร็จ',
            3 => '3. ใบเสร็จที่ออกแล้ว แต่ยังไม่บันทึกรับเงิน',
        ];

        // 9 ข้อคอลัมน์ตามที่ผู้ใช้กำหนด:
        // 1. ใบงาน 2. วันที่ออกเอกสาร 3. เลขที่ใบแจ้งหรือใบกำกับภาษี 4. วันครบกำหนดชำระ 5. ยอดจำนวนเต็ม 6. ยอดใบลดหนี้หรือเพิ่มหนี้ 7. ยอดที่ชำระแล้ว 8. ยอดค้างชำระ 9. วันที่เกินกำหนดชำระ
        $headers = [
            'A' => 'ลำดับ',
            'B' => 'ใบงาน',
            'C' => 'วันที่ออกเอกสาร',
            'D' => 'เลขที่เอกสาร',
            'E' => 'ชื่อลูกค้า',
            'F' => 'วันครบกำหนดชำระ',
            'G' => 'ยอดจำนวนเต็ม',
            'H' => 'ยอดใบลดหนี้/เพิ่มหนี้',
            'I' => 'ยอดที่ชำระแล้ว',
            'J' => 'ยอดค้างชำระ',
            'K' => 'วันที่เกินกำหนดชำระ',
        ];

        // สร้าง Sheet รวมทุกสถานะเป็น Sheet แรก
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปทุกสถานะ');

        $currentRow = 1;

        // Title
        $sheet->setCellValue('A' . $currentRow, 'รายงานสถานะลูกหนี้ (Account Receivable Report)');
        $sheet->mergeCells('A' . $currentRow . ':K' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $currentRow++;

        $filterText = 'ข้อมูล ณ วันที่ ' . date('d/m/Y H:i');
        if (!empty($startDate) || !empty($endDate)) {
            $filterText .= ' (ช่วงวันที่เอกสาร: ' . ($startDate ?: '-') . ' ถึง ' . ($endDate ?: '-') . ')';
        }
        $sheet->setCellValue('A' . $currentRow, $filterText);
        $sheet->mergeCells('A' . $currentRow . ':K' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true)->setSize(11);
        $currentRow += 2;

        $grandTotalAmount = 0;
        $grandTotalCnDn = 0;
        $grandTotalPaid = 0;
        $grandTotalRemaining = 0;

        foreach ([1, 2, 3] as $catKey) {
            if (!empty($category) && $category != $catKey) {
                continue;
            }

            $rows = $reportData[$catKey] ?? [];

            // Category Header Banner
            $sheet->setCellValue('A' . $currentRow, 'หมวด: ' . $categoryTitles[$catKey] . ' (' . count($rows) . ' รายการ)');
            $sheet->mergeCells('A' . $currentRow . ':K' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('FFFFFF');
            $headerColor = ($catKey == 1) ? '007BFF' : (($catKey == 2) ? '17A2B8' : '28A745');
            $sheet->getStyle('A' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($headerColor);
            $currentRow++;

            // Column Headers
            foreach ($headers as $col => $title) {
                $sheet->setCellValue($col . $currentRow, $title);
                $sheet->getStyle($col . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle($col . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');
                $sheet->getStyle($col . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $currentRow++;

            $startDataRow = $currentRow;
            $seq = 1;
            $catTotalAmount = 0;
            $catTotalCnDn = 0;
            $catTotalPaid = 0;
            $catTotalRemaining = 0;

            if (empty($rows)) {
                $sheet->setCellValue('A' . $currentRow, 'ไม่พบรายการค้างชำระในกลุ่มนี้');
                $sheet->mergeCells('A' . $currentRow . ':K' . $currentRow);
                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true);
                $currentRow++;
            } else {
                foreach ($rows as $item) {
                    $docDate = !empty($item['invoice_date']) ? date('d/m/Y', strtotime($item['invoice_date'])) : '-';
                    $dueDate = !empty($item['computed_due_date']) ? date('d/m/Y', strtotime($item['computed_due_date'])) : '-';

                    $sheet->setCellValue('A' . $currentRow, $seq++);
                    $sheet->setCellValueExplicit('B' . $currentRow, (string)$item['job_no'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue('C' . $currentRow, $docDate);
                    $sheet->setCellValueExplicit('D' . $currentRow, (string)$item['invoice_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue('E' . $currentRow, $item['customer_name'] ?? '-');
                    $sheet->setCellValue('F' . $currentRow, $dueDate);
                    $sheet->setCellValue('G' . $currentRow, (float)$item['total_amount']);
                    $sheet->setCellValue('H' . $currentRow, (float)$item['net_cn_dn']);
                    $sheet->setCellValue('I' . $currentRow, (float)$item['total_paid']);
                    $sheet->setCellValue('J' . $currentRow, (float)$item['remaining_amount']);
                    
                    // วันที่เกินกำหนดชำระ
                    if ($item['overdue_days'] > 0) {
                        $sheet->setCellValue('K' . $currentRow, $item['overdue_days'] . ' วัน');
                        $sheet->getStyle('K' . $currentRow)->getFont()->getColor()->setRGB('DC3545')->setBold(true);
                    } else {
                        $sheet->setCellValue('K' . $currentRow, '0 วัน (ยังไม่เกินกำหนด)');
                        $sheet->getStyle('K' . $currentRow)->getFont()->getColor()->setRGB('28A745');
                    }

                    // จัดชิดคอลัมน์
                    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('K' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // รูปแบบตัวเลขทศนิยม 2 ตำแหน่ง
                    $sheet->getStyle('G' . $currentRow . ':J' . $currentRow)->getNumberFormat()->setFormatCode('#,##0.00');

                    $catTotalAmount += (float)$item['total_amount'];
                    $catTotalCnDn += (float)$item['net_cn_dn'];
                    $catTotalPaid += (float)$item['total_paid'];
                    $catTotalRemaining += (float)$item['remaining_amount'];

                    $currentRow++;
                }

                // สรุปยอดรวมประจำกลุ่ม
                $sheet->setCellValue('A' . $currentRow, 'รวมยอด (' . $categoryTitles[$catKey] . ')');
                $sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue('G' . $currentRow, $catTotalAmount);
                $sheet->setCellValue('H' . $currentRow, $catTotalCnDn);
                $sheet->setCellValue('I' . $currentRow, $catTotalPaid);
                $sheet->setCellValue('J' . $currentRow, $catTotalRemaining);
                $sheet->setCellValue('K' . $currentRow, '');

                $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
                $sheet->getStyle('G' . $currentRow . ':J' . $currentRow)->getNumberFormat()->setFormatCode('#,##0.00');

                // ขีดเส้นใต้
                $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

                $currentRow++;
            }

            $grandTotalAmount += $catTotalAmount;
            $grandTotalCnDn += $catTotalCnDn;
            $grandTotalPaid += $catTotalPaid;
            $grandTotalRemaining += $catTotalRemaining;

            $currentRow++; // เว้นบรรทัดระหว่างหมวด
        }

        // ยอดรวมทั้งหมด (Grand Total)
        if (empty($category)) {
            $sheet->setCellValue('A' . $currentRow, 'รวมยอดทั้งสิ้นทุกหมวดสถานะ');
            $sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('G' . $currentRow, $grandTotalAmount);
            $sheet->setCellValue('H' . $currentRow, $grandTotalCnDn);
            $sheet->setCellValue('I' . $currentRow, $grandTotalPaid);
            $sheet->setCellValue('J' . $currentRow, $grandTotalRemaining);
            $sheet->setCellValue('K' . $currentRow, '');

            $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D4EDDA');
            $sheet->getStyle('G' . $currentRow . ':J' . $currentRow)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        }

        // Auto width คอลัมน์ A ถึง K
        foreach (range('A', 'K') as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        $filename = 'debtor_report_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
