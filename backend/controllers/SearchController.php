<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;
use backend\models\Job;
use backend\models\Quotation;
use backend\models\Purch;
use backend\models\PurchaseMaster;
use backend\models\Orders;
use backend\models\DeliveryNote;
use backend\models\Invoice;
use backend\models\Product;
use yii\helpers\ArrayHelper;

class SearchController extends BaseController
{
    public function actionIndex()
    {
        $searchQuery = Yii::$app->request->get('q', '');
        $dataProvider = null;
        $results = [];

        if (!empty($searchQuery)) {
            $keywords = array_filter(array_map('trim', explode(',', $searchQuery)));
            
            if (!empty($keywords)) {
                // 1. Find matching Products first
                $productQuery = Product::find();
                $prodConditions = ['or'];
                foreach ($keywords as $keyword) {
                    $prodConditions[] = ['like', 'code', $keyword];
                    $prodConditions[] = ['like', 'name', $keyword];
                }
                $productQuery->where($prodConditions);
                $matchedProducts = $productQuery->all();
                
                $productIds = ArrayHelper::getColumn($matchedProducts, 'id');
                $productCodes = ArrayHelper::getColumn($matchedProducts, 'code');

                // 2. Query Jobs
                if (class_exists('backend\models\Job')) {
                    $q = Job::find()->joinWith(['jobLines'])->groupBy('job.id');
                    $cond = ['or'];
                    foreach ($keywords as $keyword) {
                        $cond[] = ['like', 'job.job_no', $keyword];
                        $cond[] = ['like', 'job.summary_note', $keyword];
                    }
                    if (!empty($productIds)) {
                        $cond[] = ['in', 'job_line.product_id', $productIds];
                    }
                    $q->where($cond);
                    foreach ($q->all() as $m) {
                        $results[] = [
                            'type' => 'job',
                            'model' => $m,
                            'date' => $m->created_at
                        ];
                    }
                }

                // 3. Query Quotation
                if (class_exists('backend\models\Quotation')) {
                    $q = Quotation::find()->joinWith(['quotationLines'])->groupBy('quotation.id');
                    $cond = ['or'];
                    foreach ($keywords as $keyword) {
                        $cond[] = ['like', 'quotation.quotation_no', $keyword];
                    }
                    if (!empty($productIds)) {
                        $cond[] = ['in', 'quotation_line.product_id', $productIds];
                    }
                    $q->where($cond);
                    foreach ($q->all() as $m) {
                        $results[] = [
                            'type' => 'quotation',
                            'model' => $m,
                            'date' => $m->created_at
                        ];
                    }
                }

                // 4. Query Purchase Order (Purch)
                if (class_exists('backend\models\Purch')) {
                    $q = Purch::find()->joinWith(['purchLines'])->groupBy('purch.id');
                    $cond = ['or'];
                    foreach ($keywords as $keyword) {
                        $cond[] = ['like', 'purch.purch_no', $keyword];
                    }
                    if (!empty($productIds)) {
                        $cond[] = ['in', 'purch_line.product_id', $productIds];
                    }
                    $q->where($cond);
                    foreach ($q->all() as $m) {
                        $results[] = [
                            'type' => 'po',
                            'model' => $m,
                            'date' => $m->created_at
                        ];
                    }
                }

                // 5. Query Purchase Master (NPR)
                if (class_exists('backend\models\PurchaseMaster')) {
                    $q = PurchaseMaster::find()->joinWith(['purchaseDetails'])->groupBy('purchase_master.id');
                    $cond = ['or'];
                    foreach ($keywords as $keyword) {
                        $cond[] = ['like', 'purchase_master.docnum', $keyword];
                    }
                    if (!empty($productCodes)) {
                        $cond[] = ['in', 'purchase_detail.stkcod', $productCodes];
                    }
                    $q->where($cond);
                    foreach ($q->all() as $m) {
                        $results[] = [
                            'type' => 'npr',
                            'model' => $m,
                            'date' => $m->created_at
                        ];
                    }
                }

                // 6. Query Sales Order (Orders)
                if (class_exists('backend\models\Orders')) {
                    $q = Orders::find()->joinWith(['ordersLines'])->groupBy('orders.id');
                    $cond = ['or'];
                    foreach ($keywords as $keyword) {
                        $cond[] = ['like', 'orders.order_no', $keyword];
                    }
                    if (!empty($productIds)) {
                        $cond[] = ['in', 'orders_line.product_id', $productIds];
                    }
                    $q->where($cond);
                    foreach ($q->all() as $m) {
                        $results[] = [
                            'type' => 'so',
                            'model' => $m,
                            'date' => $m->created_at
                        ];
                    }
                }
                
                // 7. Query Delivery Note
                if (class_exists('backend\models\DeliveryNote')) {
                    $q = DeliveryNote::find()->joinWith(['deliveryNoteLines'])->groupBy('delivery_note.id');
                    $cond = ['or'];
                    foreach ($keywords as $keyword) {
                        $cond[] = ['like', 'delivery_note.dn_no', $keyword];
                        $cond[] = ['like', 'delivery_note_line.item_no', $keyword];
                        $cond[] = ['like', 'delivery_note_line.part_no', $keyword];
                        $cond[] = ['like', 'delivery_note_line.description', $keyword];
                    }
                    $q->where($cond);
                    foreach ($q->all() as $m) {
                        $results[] = [
                            'type' => 'dn',
                            'model' => $m,
                            'date' => $m->created_at
                        ];
                    }
                }
                
                // 8. Query Invoice
                if (class_exists('backend\models\Invoice')) {
                    $q = Invoice::find()->joinWith(['invoiceItems'])->groupBy('invoice.id');
                    $cond = ['or'];
                    foreach ($keywords as $keyword) {
                        $cond[] = ['like', 'invoice.invoice_no', $keyword];
                    }
                    if (!empty($productIds)) {
                        $cond[] = ['in', 'invoice_item.product_id', $productIds];
                    }
                    $q->where($cond);
                    foreach ($q->all() as $m) {
                        $results[] = [
                            'type' => 'invoice',
                            'model' => $m,
                            'date' => $m->created_at
                        ];
                    }
                }
            }

            // Sort results by date descending
            ArrayHelper::multisort($results, 'date', SORT_DESC);

            $dataProvider = new ArrayDataProvider([
                'allModels' => $results,
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
        }

        return $this->render('index', [
            'searchQuery' => $searchQuery,
            'dataProvider' => $dataProvider,
        ]);
    }
}
