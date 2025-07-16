<?php

namespace backend\controllers;

use backend\models\Customer;
use backend\models\CustomerSearch;
use backend\models\PositionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
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
                        'delete' => ['POST','GET'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Customer models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $pageSize = \Yii::$app->request->post("perpage");
        $searchModel = new CustomerSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $dataProvider->setSort(['defaultOrder' => ['id' => SORT_DESC]]);
        $dataProvider->pagination->pageSize = $pageSize;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'perpage' => $pageSize,
        ]);
    }

    /**
     * Displays a single Customer model.
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
     * Creates a new Customer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Customer();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $address = \Yii::$app->request->post('cus_address');
                $street = \Yii::$app->request->post('cus_street');
                $district_id = \Yii::$app->request->post('district_id');
                $city_id = \Yii::$app->request->post('city_id');
                $province_id = \Yii::$app->request->post('province_id');
                $zipcode = \Yii::$app->request->post('zipcode');

                $party_type_id = 2;

                if($model->save(false)){
                    if($address != null || $address != '') {

                            $model_address = new \common\models\AddressInfo();
                            $model_address->party_id = $model->id;
                            $model_address->party_type_id = $party_type_id;
                            $model_address->address = $address;
                            $model_address->street = $street;
                            $model_address->district_id = $district_id;
                            $model_address->city_id = $city_id;
                            $model_address->province_id = $province_id;
                            $model_address->zip_code = $zipcode;
                            $model_address->save(false);

                    }
                }
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
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $address = \Yii::$app->request->post('cus_address');
            $street = \Yii::$app->request->post('cus_street');
            $district_id = \Yii::$app->request->post('district_id');
            $city_id = \Yii::$app->request->post('city_id');
            $province_id = \Yii::$app->request->post('province_id');
            $zipcode = \Yii::$app->request->post('zipcode');

            $party_type_id = 2;

            if($model->save(false)){
                if($address != null || $address != '') {
                    $model_address_check = \common\models\AddressInfo::find()->where(['party_id' => $id,'party_type_id' => $party_type_id])->one();
                    if($model_address_check) {
                        $model_address_check->address = $address;
                        $model_address_check->street = $street;
                        $model_address_check->district_id = $district_id;
                        $model_address_check->city_id = $city_id;
                        $model_address_check->province_id = $province_id;
                        $model_address_check->zip_code = $zipcode;
                        $model_address_check->save(false);
                    }else{
                        $model_address = new \common\models\AddressInfo();
                        $model_address->party_id = $id;
                        $model_address->party_type_id = $party_type_id;
                        $model_address->address = $address;
                        $model_address->street = $street;
                        $model_address->district_id = $district_id;
                        $model_address->city_id = $city_id;
                        $model_address->province_id = $province_id;
                        $model_address->zip_code = $zipcode;
                        $model_address->save(false);
                    }
                }
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Customer model.
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
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Customer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customer::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionImportcustomer()
    {
        $uploaded = UploadedFile::getInstanceByName('file_product');
        if (!empty($uploaded)) {
            //echo "ok";return;
            $upfiles = time() . "." . $uploaded->getExtension();
            // if ($uploaded->saveAs(Yii::$app->request->baseUrl . '/uploads/files/' . $upfiles)) {
            if ($uploaded->saveAs('../web/uploads/files/products/' . $upfiles)) {
                //  echo "okk";return;
                // $myfile = Yii::$app->request->baseUrl . '/uploads/files/' . $upfiles;
                $myfile = '../web/uploads/files/products/' . $upfiles;
                $file = fopen($myfile, "r+");
                fwrite($file, "\xEF\xBB\xBF");

                setlocale(LC_ALL, 'th_TH.TIS-620');
                $i = -1;
                $res = 0;
                $data = [];
                while (($rowData = fgetcsv($file, 10000, ",")) !== FALSE) {
                    $i += 1;
                    $catid = 0;
                    $qty = 0;
                    $price = 0;
                    $cost = 0;
                    if ($rowData[2] == '' || $i == 0) {
                        continue;
                    }

                    $model_dup = \backend\models\Customer::find()->where(['name' => trim($rowData[5])])->one();
                    if ($model_dup != null) {
                        $new_stock_qty = 0;

//                        $new_unit = $this->checkUnit(trim($rowData[3]));
//                        $new_warehouse = $this->checkWarehouse(trim($rowData[4]));
//                        if($rowData[2] != null || $rowData[2] != ''){
//                            $new_stock_qty = $rowData[2];
//                        }

                        $model_dup->name = $rowData[1];
                        $model_dup->description = '';// $rowData[1];
                        $model_dup->status = 1;
                        //   $model_dup->updated_at = date('Y-m-d H:i:s');
                        if($model_dup->save(false)){
                            // $this->calStock($model_dup->id,1,$new_warehouse,$rowData[2]);
                            $res+=1;
                        }
                        continue;
                    }else{

//                        $new_unit = $this->checkUnit(trim($rowData[3]));
//                        $new_warehouse = $this->checkWarehouse(trim($rowData[4]));
                        //    echo "must new";
                        $modelx = new \backend\models\Customer();
                        $modelx->code = trim($rowData[5]);
                        $modelx->name = trim($rowData[1]);
                        $modelx->description = ''; trim($rowData[1]);
                        $modelx->status = 1;
                        //
                        if ($modelx->save(false)) {
                            //  $this->calStock($modelx->id,1,$new_warehouse,$rowData[2]);
                            $res += 1;
                        }
                    }


                }
                //    print_r($qty_text);return;

                if ($res > 0) {
                    $session = \Yii::$app->session;
                    $session->setFlash('msg', 'นำเข้าข้อมูลเรียบร้อย');
                    return $this->redirect(['index']);
                } else {
                    $session = \Yii::$app->session;
                    $session->setFlash('msg-error', 'พบข้อมผิดพลาดนะ');
                    return $this->redirect(['index']);
                }
                // }
                fclose($file);
//            }
//        }
            }
        }
    }
}
