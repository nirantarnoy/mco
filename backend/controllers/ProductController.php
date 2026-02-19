<?php

namespace backend\controllers;

use backend\models\Product;
use backend\models\ProductSearch;
use backend\models\StockTrans;
use backend\models\WarehouseSearch;
use common\models\JournalTrans;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use Yii;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends BaseController
{
    public $enableCsrfValidation = false;

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
                        'delete' => ['POST', 'GET'],
                    ],
                ],
//                'access' => [
//                    'class' => AccessControl::className(),
//                    'denyCallback' => function ($rule, $action) {
//                        throw new ForbiddenHttpException('คุณไม่ได้รับอนุญาติให้เข้าใช้งาน!');
//                    },
//                    'rules' => [
//                        [
//                            'allow' => true,
//                            'roles' => ['@'],
//                            'matchCallback' => function ($rule, $action) {
//                                $currentRoute = \Yii::$app->controller->getRoute();
//                                if (\Yii::$app->user->can($currentRoute)) {
//                                    return true;
//                                }
//                            }
//                        ]
//                    ]
//                ],
            ]
        );
    }



    /**
     * Checks if the current session is valid.
     *
     * @return bool
     */


    /**
     * Lists all Product models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $viewstatus = 1;

        if (\Yii::$app->request->get('viewstatus') != null) {
            $viewstatus = \Yii::$app->request->get('viewstatus');
        }

        $pageSize = \Yii::$app->request->post("perpage");
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
//        if($viewstatus ==1){
//            $dataProvider->query->andFilterWhere(['status'=>$viewstatus]);
//        }
//        if($viewstatus == 2){
//            $dataProvider->query->andFilterWhere(['status'=>0]);
//        }

        $dataProvider->setSort(['defaultOrder' => ['name' => SORT_ASC]]);
        $dataProvider->pagination->pageSize = $pageSize;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'perpage' => $pageSize,
            'viewstatus' => $viewstatus,
        ]);
    }

    /**
     * Displays a single Product model.
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
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Product();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $line_warehouse = \Yii::$app->request->post('warehouse_id');
                $line_qty = \Yii::$app->request->post('line_qty');
                $line_exp_date = \Yii::$app->request->post('line_exp_date');

               //  $model->code = $model->name;
                if($model->validate()){
                    if ($model->save()) {
                        $uploaded = UploadedFile::getInstancesByName('product_photo');
                        $uploaded2 = UploadedFile::getInstanceByName('product_photo_2');

                        if (!empty($uploaded)) {
                            if(count($uploaded)>2){
                                \Yii::$app->session->setFlash('error', 'ไม่สามารถอัพโหลดรูปเกิน 2 รูป');
                                return $this->redirect(['update', 'id' => $model->id]);
                            }
                            $loop = 1;
                            foreach ($uploaded as $file){
                                if($loop == 1){
                                    $upfiles = "photo_" . time() . "." . $file->getExtension();
                                    if ($file->saveAs('uploads/product_photo/' . $upfiles)) {
                                        \backend\models\Product::updateAll(['photo' => $upfiles], ['id' => $model->id]);
                                    }
                                }else{
                                    $upfiles = "photo_" . time() . "." . $file->getExtension();
                                    if ($file->saveAs('uploads/product_photo/' . $upfiles)) {
                                        \backend\models\Product::updateAll(['photo2' => $upfiles], ['id' => $model->id]);
                                    }
                                }
                                $loop++;
                            }
                        }
//                        if (!empty($uploaded2)) {
//                            $upfiles2 = "photo_" . time() . "." . $uploaded2->getExtension();
//                            if ($uploaded2->saveAs('uploads/product_photo/' . $upfiles2)) {
//                                \backend\models\Product::updateAll(['photo_2' => $upfiles2], ['id' => $model->id]);
//                            }
//
//                        }

                        if ($line_warehouse != null) {
                            $model_journal_trans = new \backend\models\JournalTrans();
                            $model_journal_trans->trans_date = date('Y-m-d H:i:s');
                            $model_journal_trans->journal_no = '';
                            $model_journal_trans->remark = 'รับยอดยกมา/สร้างสินค้าใหม่';
                            $model_journal_trans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_ADJUST_STOCK;
                            $model_journal_trans->status = \backend\models\JournalTrans::STATUS_APPROVED;
                            $model_journal_trans->stock_type_id = \backend\models\JournalTrans::STOCK_TYPE_IN;
                            $model_journal_trans->warehouse_id = 0;

                            if ($model_journal_trans->save(false)) {
                                for ($i = 0; $i < count($line_warehouse); $i++) {
                                    $qty = (float)$line_qty[$i];
                                    if ($line_warehouse[$i] <= 0 || $qty <= 0) {
                                        continue;
                                    }

                                    $model_trans = new \backend\models\JournalTransLine();
                                    $model_trans->product_id = $model->id;
                                    $model_trans->journal_trans_id = $model_journal_trans->id;
                                    $model_trans->warehouse_id = $line_warehouse[$i];
                                    $model_trans->qty = $qty;
                                    $model_trans->status = 1;

                                    if ($model_trans->save(false)) {
                                        $model_stock_trans = new StockTrans();
                                        $model_stock_trans->trans_date = date('Y-m-d H:i:s');
                                        $model_stock_trans->journal_trans_id = $model_journal_trans->id;
                                        $model_stock_trans->product_id = $model->id;
                                        $model_stock_trans->warehouse_id = $line_warehouse[$i];
                                        $model_stock_trans->qty = $qty;
                                        $model_stock_trans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_ADJUST_STOCK;
                                        $model_stock_trans->created_at = date('Y-m-d H:i:s');
                                        $model_stock_trans->created_by = Yii::$app->user->id;
                                        $model_stock_trans->updated_at = date('Y-m-d H:i:s');
                                        $model_stock_trans->stock_type_id = \backend\models\JournalTrans::STOCK_TYPE_IN;
                                        $model_stock_trans->save(false);

                                        \backend\models\StockSum::updateStockIn($model->id, $line_warehouse[$i], $qty);
                                    }
                                }
                            }
                        }
                    }
                    $this->updateProductStock($model->id);
                    \Yii::$app->session->setFlash('success', 'บันทึกสินค้าเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model_line = \common\models\StockSum::find()->where(['product_id'=>$id])->all();
        $work_photo = '';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $uploaded = UploadedFile::getInstancesByName('product_photo');


            $line_rec_id = \Yii::$app->request->post('line_rec_id');
            $removelist = \Yii::$app->request->post('remove_list');
            $old_photo = \Yii::$app->request->post('old_photo');
            $old_photo2 = \Yii::$app->request->post('old_photo2');

            $line_warehouse = \Yii::$app->request->post('warehouse_id');
            $line_qty = \Yii::$app->request->post('line_qty');

            //  print_r($line_customer_rec_id);return;

            if ($model->save(false)) {
                if (!empty($uploaded)) {
                    if(count($uploaded)>2){
                        \Yii::$app->session->setFlash('error', 'ไม่สามารถอัพโหลดรูปเกิน 2 รูป');
                        return $this->redirect(['update', 'id' => $model->id]);
                    }
                    $loop = 1;
                    foreach ($uploaded as $file){
                        if($loop == 1){
                            $upfiles = "photo_".$loop . time() . "." . $file->getExtension();
                            if ($file->saveAs('uploads/product_photo/' . $upfiles)) {
                                \backend\models\Product::updateAll(['photo' => $upfiles], ['id' => $model->id]);
                                if($old_photo != null){
                                    if(file_exists('uploads/product_photo/'.$old_photo)){
                                        unlink('uploads/product_photo/'.$old_photo);
                                    }
                                }
                            }
                        }else{
                            $upfiles = "photo_".$loop . time() . "." . $file->getExtension();
                            if ($file->saveAs('uploads/product_photo/' . $upfiles)) {
                                \backend\models\Product::updateAll(['photo2' => $upfiles], ['id' => $model->id]);
                                if($old_photo != null){
                                    if($old_photo2!=null){
                                        if(file_exists('uploads/product_photo/'.$old_photo2)){
                                            unlink('uploads/product_photo/'.$old_photo2);
                                        }
                                    }
                                }
                            }
                        }
                      $loop++;
                    }

                }

                // Check if user toggled stock adjustment
                // Using fallback to check POST directly if load() safe attribute has any issue
                $post_product = \Yii::$app->request->post('Product');
                $is_adjust = isset($post_product['is_adjust_stock']) && ($post_product['is_adjust_stock'] == 1 || $post_product['is_adjust_stock'] == 'on');

                if ($is_adjust && $line_warehouse != null) {
                    $model_journal_trans = new \backend\models\JournalTrans();
                    $model_journal_trans->trans_date = date('Y-m-d H:i:s');
                    $model_journal_trans->journal_no = ''; 
                    $model_journal_trans->remark = 'ปรับปรุงยอดสต๊อกตอนแก้ไขสินค้า';
                    $model_journal_trans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_ADJUST_STOCK;
                    $model_journal_trans->status = \backend\models\JournalTrans::STATUS_APPROVED; 
                    $model_journal_trans->stock_type_id = \backend\models\JournalTrans::STOCK_TYPE_IN; 
                    $model_journal_trans->warehouse_id = 0;

                    if ($model_journal_trans->save(false)) {
                        for ($i = 0; $i <= count($line_warehouse) - 1; $i++) {
                            if ($line_warehouse[$i] == null || $line_qty[$i] === null || $line_warehouse[$i] <= 0 || $line_qty[$i] === '') {
                                continue;
                            }

                            $old_stock = \backend\models\StockSum::find()->where(['product_id' => $model->id, 'warehouse_id' => $line_warehouse[$i]])->one();
                            $old_qty = $old_stock ? $old_stock->qty : 0;
                            $new_qty = (float)$line_qty[$i];
                            $delta = $new_qty - $old_qty;

                            // Skip if no change in this warehouse
                            if ($delta == 0) continue;

                            $stock_type = ($delta > 0) ? \backend\models\JournalTrans::STOCK_TYPE_IN : \backend\models\JournalTrans::STOCK_TYPE_OUT;
                            $abs_qty = abs($delta);

                            $model_trans = new \backend\models\JournalTransLine();
                            $model_trans->product_id = $model->id;
                            $model_trans->journal_trans_id = $model_journal_trans->id;
                            $model_trans->warehouse_id = $line_warehouse[$i];
                            $model_trans->qty = $abs_qty;
                            $model_trans->status = 1;

                            if ($model_trans->save(false)) {
                                $model_stock_trans = new StockTrans();
                                $model_stock_trans->trans_date = date('Y-m-d H:i:s');
                                $model_stock_trans->journal_trans_id = $model_journal_trans->id;
                                $model_stock_trans->product_id = $model->id;
                                $model_stock_trans->warehouse_id = $line_warehouse[$i];
                                $model_stock_trans->qty = $abs_qty;
                                $model_stock_trans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_ADJUST_STOCK;
                                $model_stock_trans->created_at = date('Y-m-d H:i:s');
                                $model_stock_trans->created_by = Yii::$app->user->id;
                                $model_stock_trans->updated_at = date('Y-m-d H:i:s');
                                $model_stock_trans->stock_type_id = $stock_type;
                                $model_stock_trans->save(false);

                                if ($delta > 0) {
                                    \backend\models\StockSum::updateStockIn($model->id, $line_warehouse[$i], $abs_qty);
                                } else {
                                    \backend\models\StockSum::updateStockOut($model->id, $line_warehouse[$i], $abs_qty);
                                }
                            }
                        }
                        \Yii::$app->session->setFlash('success', 'ปรับปรุงสต๊อกสินค้าเรียบร้อยแล้ว');
                    }
                }

                $this->updateProductStock($model->id);

                if($removelist!=null){
                    $xdel = explode(',', $removelist);
                    for($i=0;$i<count($xdel);$i++){
                        \backend\models\StockSum::deleteAll(['id'=>$xdel[$i]]);
                    }

                    $this->updateProductStock($model->id);
                }

            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'work_photo' => $work_photo,
            'model_line' => $model_line,
            'model_customer_line'=>null,
        ]);
    }

    function checkEditChange($product_id,$warehouse_id,$qty){
        $res = 0;
        $model = \backend\models\StockSum::find()->where(['product_id'=>$product_id,'warehouse_id'=>$warehouse_id])->one();
        if($model){
            if($model->qty != $qty){ // is edited
                $res = 1;
            }
        }else{
            $res = 1;
        }
        return $res;
    }

    function updateProductStock($product_id){
        if($product_id){
            $total_stock = \backend\models\StockSum::find()
                ->where(['product_id' => $product_id])
                ->sum('qty + COALESCE(reserv_qty, 0)');

            \backend\models\Product::updateAll(
                ['stock_qty' => $total_stock ?: 0],
                ['id' => $product_id]
            );
        }
    }

    /**
     * Deletes an existing Product model.
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

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionImportpage()
    {
        return $this->render('_import');
    }
//    public function actionImportproduct()
//    {
//        $uploaded = UploadedFile::getInstanceByName('file_import');
//        if (!empty($uploaded)) {
//            //echo "ok";return;
//            $upfiles = time() . "." . $uploaded->getExtension();
//            // if ($uploaded->saveAs(Yii::$app->request->baseUrl . '/uploads/files/' . $upfiles)) {
//            if ($uploaded->saveAs('../web/uploads/files/products/' . $upfiles)) {
//                //  echo "okk";return;
//                // $myfile = Yii::$app->request->baseUrl . '/uploads/files/' . $upfiles;
//                $myfile = '../web/uploads/files/products/' . $upfiles;
//                $file = fopen($myfile, "r+");
//                fwrite($file, "\xEF\xBB\xBF");
//
//                setlocale(LC_ALL, 'th_TH.TIS-620');
//                $i = -1;
//                $res = 0;
//                $data = [];
//                while (($rowData = fgetcsv($file, 10000, ",")) !== FALSE) {
//                    $i += 1;
//                    $catid = 0;
//                    $qty = 0;
//                    $price = 0;
//                    $cost = 0;
//                    if ($rowData[1] == '' || $i == 0) {
//                        continue;
//                    }
//
//                    $model_dup = \backend\models\Product::find()->where(['sku' => trim($rowData[1])])->one();
//                    if ($model_dup != null) {
//                        continue;
//                    }
//
//
//                    $modelx = new \backend\models\Product();
//                    // $modelx->code = $rowData[0];
//                    $modelx->code = $rowData[2];
//                    $modelx->sku = $rowData[2];
//                    $modelx->name = $rowData[1];
//                    $modelx->barcode = $rowData[3];
//                    $modelx->total_qty = $rowData[4];
//                    $modelx->sale_price = $rowData[5];
//                    $modelx->status = 1;
//                    if ($modelx->save(false)) {
//                        $res += 1;
//                    }
//                }
//                //    print_r($qty_text);return;
//
//                if ($res > 0) {
//                    $session = \Yii::$app->session;
//                    $session->setFlash('msg', 'นำเข้าข้อมูลเรียบร้อย');
//                    return $this->redirect(['index']);
//                } else {
//                    $session = \Yii::$app->session;
//                    $session->setFlash('msg-error', 'พบข้อมผิดพลาดนะ');
//                    return $this->redirect(['index']);
//                }
//                // }
//                fclose($file);
////            }
////        }
//            }
//            echo "ok";
//        }
//    }

    public function actionFinditem()
    {
        $html = '';
        $has_data = 0;
        //$model = \backend\models\Workqueue::find()->where(['is_invoice' => 0])->all();
        // $model = \backend\models\StockSum::find()->where(['warehouse_id' => 7])->all();
        $model = \backend\models\Product::find()->where(['status'=>1])->all();
        if ($model) {
            $has_data = 1;
            foreach ($model as $value) {
                $onhand_qty = $this->getProductOnhand($value->id);
                $code = $value->name;
                $name = $value->name;
                $price = 0;
                $unit_id = $value->unit_id;
                $unit_name = \backend\models\Unit::findName($unit_id);
                $is_drummy  = '' ;// $value->is_special;
                $html .= '<tr>';
                $html .= '<td style="text-align: center">
                            <div class="btn btn-outline-success btn-sm" onclick="addselecteditem($(this))" data-var="' . $value->id . '">เลือก</div>
                            <input type="hidden" class="line-find-item-id" value="' . $value->id . '">
                            <input type="hidden" class="line-find-item-code" value="' . $code . '">
                            <input type="hidden" class="line-find-item-name" value="' . $name . '">
                            <input type="hidden" class="line-find-price" value="' . $price . '">
                            <input type="hidden" class="line-find-unit-id" value="' . $unit_id . '">
                            <input type="hidden" class="line-find-unit-name" value="' . $unit_name . '">
                            <input type="hidden" class="line-find-is-drummy" value="' . $is_drummy . '">
                           </td>';
                $html .= '<td style="text-align: left">' . $code . '</td>';
                $html .= '<td style="text-align: left">' . $name . '</td>';
                $html .= '<td style="text-align: left">' . $unit_name . '</td>';
                $html .= '<td style="text-align: left">' . $onhand_qty . '</td>';
                $html .= '</tr>';
            }
        }

        if ($has_data == 0) {
            $html .= '<tr>';
            $html .= '<td colspan="5" style="text-align: center;color: red;">ไม่พบข้อมูล</td>';
            $html .= '</tr>';
        }
        echo $html;
    }

    function getProductOnhand($product_id){
        return \common\models\StockSum::find()->where(['product_id' => $product_id])->sum('qty');
    }

    public function actionImportproduct()
    {
        $uploaded = UploadedFile::getInstanceByName('file_product');
        if (!empty($uploaded)) {
            $upfiles = time() . "." . $uploaded->getExtension();
            if ($uploaded->saveAs('../web/uploads/files/products/' . $upfiles)) {
                $myfile = '../web/uploads/files/products/' . $upfiles;
                $file = fopen($myfile, "r");
                
                setlocale(LC_ALL, 'th_TH.TIS-620');
                $i = -1;
                $res = 0;
                while (($rowData = fgetcsv($file, 10000, ",")) !== FALSE) {
                    $i += 1;
                    if ($i == 0 || empty($rowData[5])) {
                        continue;
                    }

                    $product_name = trim($rowData[0]);
                    $brand_name = trim($rowData[1]);
                    $qty = (float)str_replace(",", "", $rowData[2]);
                    $unit_name = trim($rowData[3]);
                    $price = (float)str_replace(",", "", $rowData[4]);
                    $warehouse_name = trim($rowData[5]);
                    $product_code = trim($rowData[6]);

                    $new_unit = $this->checkUnit($unit_name);
                    $new_warehouse = $this->checkWarehouse($warehouse_name);
                    $new_brand = !empty($brand_name) ? $this->checkBrand($brand_name) : 0;

                    $model_dup = \backend\models\Product::find()->where(['code' => $product_code])->one();
                    if ($model_dup != null) {
                        $model_dup->name = $product_name;
                        $model_dup->description = $product_name;
                        $model_dup->unit_id = $new_unit;
                        $model_dup->brand_id = $new_brand;
                        $model_dup->cost_price = $price;
                        $model_dup->sale_price = $price;
                        if($model_dup->save(false)){
                            \backend\models\StockSum::updateStockIn($model_dup->id, $new_warehouse, $qty);
                            $res += 1;
                        }
                    } else {
                        $modelx = new \backend\models\Product();
                        $modelx->code = $product_code;
                        $modelx->name = $product_name;
                        $modelx->description = $product_name;
                        $modelx->product_group_id = 0;
                        $modelx->brand_id = $new_brand;
                        $modelx->product_type_id = 1;
                        $modelx->type_id = 1;
                        $modelx->unit_id = $new_unit;
                        $modelx->status = 1;
                        $modelx->cost_price = $price;
                        $modelx->sale_price = $price;
                        $modelx->stock_qty = $qty;
                        $modelx->company_id = \Yii::$app->session->get('company_id');
                        
                        if ($modelx->save(false)) {
                            \backend\models\StockSum::updateStockIn($modelx->id, $new_warehouse, $qty);
                            $res += 1;
                        }
                    }
                }
                fclose($file);

                if ($res > 0) {
                    $session = \Yii::$app->session;
                    $session->setFlash('msg', 'นำเข้าข้อมูลเรียบร้อย ' . $res . ' รายการ');
                    return $this->redirect(['index']);
                } else {
                    $session = \Yii::$app->session;
                    $session->setFlash('msg-error', 'ไม่พบข้อมูลที่สามารถนำเข้าได้');
                    return $this->redirect(['index']);
                }
            }
        }
    }


    public function checkUnit($name){
        $model = \common\models\Unit::find()->where(['name'=>$name])->one();
        if($model){
            return $model->id;
        }else{
            $model = new \common\models\Unit();
            $model->name = $name;
            $model->description = $name;
            $model->status = 1;
            if($model->save(false)){
                return $model->id;
            }
        }
    }
    public function checkBrand($name){
        $model = \common\models\ProductBrand::find()->where(['name'=>$name])->one();
        if($model){
            return $model->id;
        }else{
            $model = new \common\models\ProductBrand();
            $model->name = $name;
            $model->description = $name;
            $model->status = 1;
            if($model->save(false)){
                return $model->id;
            }
        }
    }

    public function checkWarehouse($name){
        $model = \common\models\Warehouse::find()->where(['name'=>$name])->one();
        if($model){
            return $model->id;
        }else{
            $model = new \common\models\Warehouse();
            $model->name = $name;
            $model->description = $name;
            $model->status = 1;
            if($model->save(false)){
                return $model->id;
            }
        }
    }

    /**
     * AJAX action for Select2 widget
     * Returns product list in JSON format with stock information
     */
    public function actionProductList($q = null, $page = 1, $warehouse_id = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $limit = 20; // Number of items per page
        $offset = ($page - 1) * $limit;

        $query = Product::find()
            ->alias('p')
            ->select([
                'p.id',
                'p.code',
                'p.name',
                'p.sale_price',
                'p.unit_id',
                'p.photo',
                // ถ้ามี stock table แยกตาม warehouse
                // 'COALESCE(s.qty, 0) as stock_qty'
            ])
            ->where(['p.status' => 1]); // Only active products

        // Join กับ stock table ถ้ามี
        /*
        if ($warehouse_id) {
            $query->leftJoin(['s' => 'product_stock'],
                'p.id = s.product_id AND s.warehouse_id = :warehouse_id',
                [':warehouse_id' => $warehouse_id]
            );
        }
        */

        if (!empty($q)) {
            $query->andWhere(['or',
                ['like', 'p.code', $q],
                ['like', 'p.name', $q],
                ['like', 'p.description', $q],
            ]);
        }

        $countQuery = clone $query;
        $count = $countQuery->count();

        $products = $query
            ->orderBy(['p.name' => SORT_ASC])
            ->limit($limit)
            ->offset($offset)
            ->asArray()
            ->all();

        $results = [];
        foreach ($products as $product) {
            // Get stock quantity for specific warehouse
            $stockQty = $this->getProductStock($product['id'], $warehouse_id);

            // Get unit name
            $unit = \backend\models\Unit::findOne($product['unit_id']);
            $unitName = $unit ? $unit->name : 'ชิ้น';

            $results[] = [
                'id' => $product['id'],
                'text' => '[' . $product['code'] . '] ' . $product['name'],
                'code' => $product['code'],
                'name' => $product['name'],
                'price' => $product['sale_price'],
                'stock_qty' => $stockQty,
                'unit' => $unitName,
                'photo' => $product['photo'],
            ];
        }

        return [
            'results' => $results,
            'pagination' => [
                'more' => ($offset + $limit) < $count,
            ],
        ];
    }

    /**
     * Get product details with stock information
     */
    public function actionGetProductDetail($id, $warehouse_id = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $product = Product::find()
            ->where(['id' => $id])
            ->one();

        if ($product) {
            $stockQty = $this->getProductStock($id, $warehouse_id);
            $unit = \backend\models\Unit::findOne($product->unit_id);

            return [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'unit_price' => $product->cost_price,
                'cost_price' => $product->cost_price,
                'sale_price' => $product->sale_price,
                'stock_qty' => $stockQty,
                'unit' => $unit ? $unit->name : 'ชิ้น',
                'description' => $product->description,
                'photo' => $product->photo,
            ];
        }

        return null;
    }

    /**
     * Get product stock for specific warehouse
     */
    private function getProductStock($product_id, $warehouse_id = null)
    {
        // ถ้ามี table product_stock แยกตาม warehouse
        /*
        if ($warehouse_id) {
            $stock = ProductStock::find()
                ->where(['product_id' => $product_id, 'warehouse_id' => $warehouse_id])
                ->one();
            return $stock ? $stock->qty : 0;
        }
        */

        // ถ้าเก็บ stock ใน product table
        $product = Product::findOne($product_id);
        return $product ? $product->stock_qty : 0;

        // หรือคำนวณจาก journal_trans
        /*
        $inQty = (new Query())
            ->from('journal_trans_line jtl')
            ->innerJoin('journal_trans jt', 'jtl.journal_trans_id = jt.id')
            ->where(['jtl.product_id' => $product_id])
            ->andWhere(['jt.trans_type_id' => 1]) // สมมติ 1 = รับเข้า
            ->andWhere(['jt.status' => 1]);

        if ($warehouse_id) {
            $inQty->andWhere(['jt.warehouse_id' => $warehouse_id]);
        }

        $totalIn = $inQty->sum('jtl.qty') ?: 0;

        // คำนวณจำนวนที่ออกไป
        $outQty = (new Query())
            ->from('journal_trans_line jtl')
            ->innerJoin('journal_trans jt', 'jtl.journal_trans_id = jt.id')
            ->where(['jtl.product_id' => $product_id])
            ->andWhere(['jt.trans_type_id' => 2]) // สมมติ 2 = จ่ายออก
            ->andWhere(['jt.status' => 1]);

        if ($warehouse_id) {
            $outQty->andWhere(['jt.warehouse_id' => $warehouse_id]);
        }

        $totalOut = $outQty->sum('jtl.qty') ?: 0;

        return $totalIn - $totalOut;
        */
    }

    /**
     * Check product availability before save
     */
    public function actionCheckStock()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $product_id = \Yii::$app->request->post('product_id');
        $warehouse_id = \Yii::$app->request->post('warehouse_id');
        $qty = \Yii::$app->request->post('qty', 0);

        $stockQty = $this->getProductStock($product_id, $warehouse_id);

        return [
            'available' => $stockQty >= $qty,
            'stock_qty' => $stockQty,
            'requested_qty' => $qty,
            'message' => $stockQty >= $qty ?
                'สินค้ามีเพียงพอ' :
                'สินค้าไม่เพียงพอ (คงเหลือ: ' . $stockQty . ')'
        ];
    }

    /**
     * Batch check stock for multiple products
     */
    public function actionBatchCheckStock()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $items = \Yii::$app->request->post('items', []);
        $warehouse_id = \Yii::$app->request->post('warehouse_id');

        $results = [];
        $hasError = false;

        foreach ($items as $item) {
            $product_id = $item['product_id'] ?? null;
            $qty = $item['qty'] ?? 0;

            if ($product_id) {
                $stockQty = $this->getProductStock($product_id, $warehouse_id);
                $product = Product::findOne($product_id);

                $available = $stockQty >= $qty;
                if (!$available) {
                    $hasError = true;
                }

                $results[] = [
                    'product_id' => $product_id,
                    'product_name' => $product ? $product->name : '',
                    'available' => $available,
                    'stock_qty' => $stockQty,
                    'requested_qty' => $qty,
                ];
            }
        }

        return [
            'success' => !$hasError,
            'items' => $results,
            'message' => $hasError ? 'มีสินค้าบางรายการไม่เพียงพอ' : 'สินค้าทุกรายการมีเพียงพอ'
        ];
    }

    public function actionBulkDelete()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ids = \Yii::$app->request->post('ids', []);
        if (!empty($ids)) {
            Product::deleteAll(['id' => $ids]);
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'No IDs received'];
    }

    /**
     * Recalculate stock balance from transaction history
     * @param int $id
     * @return Response
     */
    public function actionRecalculateStock($id)
    {
        $model = $this->findModel($id);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 1. Reset all StockSum for this product to 0
            \backend\models\StockSum::updateAll(['qty' => 0], ['product_id' => $id]);

            // 2. Fetch all completed/approved StockTrans
            $history = \backend\models\StockTrans::find()
                ->where(['product_id' => $id])
                ->all();

            foreach ($history as $trans) {
                $qty = $trans->qty;
                $warehouse_id = $trans->warehouse_id;
                
                // Determine direction
                $direction = 1; // Default to IN
                if ($trans->stock_type_id == 2) {
                    $direction = -1;
                } else if (!$trans->stock_type_id) {
                    // Fallback logic if stock_type_id is 0
                    if (in_array($trans->trans_type_id, [2, 3, 5])) { // Cancel POR, Issue, Borrow
                        $direction = -1;
                    }
                }

                // Update StockSum
                \backend\models\StockSum::updateStock($id, $warehouse_id, $qty, $direction);
            }

            // 3. Final sync for total product stock
            $totalStock = \backend\models\StockSum::find()
                ->where(['product_id' => $id])
                ->sum('qty') ?: 0;
            
            $model->stock_qty = $totalStock;
            $model->save(false);

            $transaction->commit();
            \Yii::$app->session->setFlash('success', 'Recalculated stock balance successfully. New stock: ' . $totalStock);
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->setFlash('error', 'Error recalculating stock: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the Product model based on its primary key value.
     */
    // In your controller
    public function actionPrintRepair($id = null)
    {
      //  $this->layout = 'print'; // Use minimal print layout
        return $this->render('_printrepair');
    }

    public function actionPrintTag()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $ids = \Yii::$app->request->post('ids');
        if (!empty($ids)) {
             $sql = "SELECT p.id, p.code, p.name,p.description,b.name as brand_name,sum(s.qty) as stock_qty FROM product as p INNER JOIN product_brand as b ON p.brand_id = b.id LEFT JOIN stock_sum as s ON p.id = s.product_id WHERE p.id IN (".$ids.") ORDER BY p.code";
             $products = \Yii::$app->db->createCommand($sql)->queryAll();
             $this->render('_print-tag', ['selectedProducts' => $products]);
        }

    }

    public function actionExportProducts()
    {
        // Get data from your model
        // $users = Product::find()->joinWith('StockSum')->all();

        $users = null;
        $sql = "SELECT w.name as warehouse_name,st.qty,p.code,p.name,p.description,p.product_group_id,p.unit_id,p.brand_id,p.remark,p.cost_price,u.name as unit_name
                FROM product as p 
                    left join stock_sum as st on p.id = st.product_id 
                    left join unit as u on p.unit_id = u.id
                    left join warehouse as w on st.warehouse_id = w.id ORDER BY p.code ASC";
        $users = \Yii::$app->db->createCommand($sql)->queryAll();

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('MCO GROUP')
            ->setLastModifiedBy('MCO GROUP')
            ->setTitle('Product Export')
            ->setSubject('Product Data')
            ->setDescription('Exported product data from the application');

        // Set column headers
        $headers = [
            'A1' => 'ลำดับ',
            'B1' => 'รายละเอียด',
            'C1' => 'คงเหลือ',
            'D1' => 'หน่วยนับ',
            'E1' => 'ที่จัดเก็บ',
            'F1' => 'รหัสสินค้า',
            'G1' => 'ราคา',
        ];

        // Apply headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style the header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Fill data rows
        $row = 2;
//        foreach ($users as $user) {
//            $sheet->setCellValue('A' . $row, $user->name);
//            $sheet->setCellValue('B' . $row, $user->description);
//            $sheet->setCellValue('C' . $row, $user->product_group_id);
//            $sheet->setCellValue('D' . $row, $user->unit_id);
//            $sheet->setCellValue('E' . $row, $user->brand_id);
//            $sheet->setCellValue('F' . $row, $user->stock_qty);
//            $sheet->setCellValue('G' . $row, $user->remark);
//            $sheet->setCellValue('H' . $row, $user->StockSum->warehouse_id);
//            $row++;
//        }
        for ($i = 0; $i < count($users); $i++) {
            $sheet->setCellValue('A' . $row, ($i + 1));
            $sheet->setCellValue('B' . $row, $users[$i]['description']);
            $sheet->setCellValue('C' . $row, $users[$i]['qty']==null?0:$users[$i]['qty']);
            $sheet->setCellValue('D' . $row, $users[$i]['unit_name']);
            $sheet->setCellValue('E' . $row, $users[$i]['warehouse_name']);
            $sheet->setCellValue('F' . $row, $users[$i]['code']);
            $sheet->setCellValue('G' . $row, $users[$i]['cost_price']);
            $row++;
        }

        // Apply borders to data
        $dataRange = 'A1:H' . ($row - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');

        // Set response headers for download
        \Yii::$app->response->format = Response::FORMAT_RAW;
        \Yii::$app->response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        \Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="products_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        \Yii::$app->response->headers->add('Cache-Control', 'max-age=0');

        // Write file to output
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $content;
    }

    public function actionGetlastproduct(){
        $product = null;
        $group_id = \Yii::$app->request->post('group_id');
        if($group_id != null && $group_id > 0){
           // $product = Product::find()->where(['product_group_id'=>$group_id])->orderBy(['id'=>SORT_DESC])->limit(1)->one();
            $product = Product::find()->where(['product_group_id'=>$group_id])->orderBy(['code'=>SORT_DESC])->limit(1)->one();
            if($product != null){
               echo $this->getlastproductcode($product->code);
               // echo $product->code;
            }else{
                echo '';
            }
        }else{
            echo '';
        }
    }

    public function getlastproductcode($code)
    {
        $product_code = 'NOT FOUND';
        $prefix = substr($code, 0, 4);
        if($code != null) {
            // Find last number for this type and date
            $lastRecord = Product::find()
                ->where(['like', 'code', $prefix])
                ->orderBy(['code' => SORT_DESC])
                ->one();

            if ($lastRecord) {
                $lastNumber = intval(substr($lastRecord->code, -5));
                $newNumber = $lastNumber + 1;
                $product_code = $lastNumber;
            } else {
                $newNumber = 1;
            }

            $product_code = $prefix . sprintf('%05d', $newNumber);
        }

        return $product_code;
    }

}
