<?php

namespace backend\controllers;

use backend\models\Customer;
use backend\models\CustomerSearch;
use backend\models\PositionSearch;
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

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends BaseController
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
                    if ($rowData[1] == '' || $i == 0) {
                        continue;
                    }

                    $model_dup = \backend\models\Customer::find()->where(['code' => trim($rowData[0])])->one();
                    if ($model_dup != null) {
                        $new_stock_qty = 0;

//                        $new_unit = $this->checkUnit(trim($rowData[3]));
//                        $new_warehouse = $this->checkWarehouse(trim($rowData[4]));
//                        if($rowData[2] != null || $rowData[2] != ''){
//                            $new_stock_qty = $rowData[2];
//                        }

                        $model_dup->name = trim($rowData[1]);
                        $model_dup->description = '';// $rowData[1];
                        $model_dup->customer_group_id = 0;
                        $model_dup->home_number = trim($rowData[2]);
                        $model_dup->street = trim($rowData[3]);
                        $model_dup->aisle = trim($rowData[4]);
                        $model_dup->district_name = trim($rowData[5]);
                        $model_dup->city_name = trim($rowData[6]);
                        $model_dup->province_name = trim($rowData[7]);
                        $model_dup->zipcode = trim($rowData[8]);
                        $model_dup->taxid = trim($rowData[9]);
                        $model_dup->is_head = 1;// trim($rowData[10]);
                        $model_dup->branch_name = trim($rowData[11]);
                        $model_dup->contact_name = trim($rowData[12]);
                        $model_dup->phone = trim($rowData[13]);
                        $model_dup->email = trim($rowData[14]);
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
                        $modelx->code = trim($rowData[0]);
                        $modelx->name = trim($rowData[1]);
                        $modelx->description = '';// $rowData[1];
                        $modelx->customer_group_id = 0;
                        $modelx->home_number = trim($rowData[2]);
                        $modelx->street = trim($rowData[3]);
                        $modelx->aisle = trim($rowData[4]);
                        $modelx->district_name = trim($rowData[5]);
                        $modelx->city_name = trim($rowData[6]);
                        $modelx->province_name = trim($rowData[7]);
                        $modelx->zipcode = trim($rowData[8]);
                        $modelx->taxid = trim($rowData[9]);
                        $modelx->is_head = 1;// trim($rowData[10]);
                        $modelx->branch_name = trim($rowData[11]);
                        $modelx->contact_name = trim($rowData[12]);
                        $modelx->phone = trim($rowData[13]);
                        $modelx->email = trim($rowData[14]);
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
    public function actionExportCustomers()
    {
        // Get data from your model
        // $users = Product::find()->joinWith('StockSum')->all();

        $users = null;
        $sql = "SELECT * FROM customer ORDER BY id ASC";
        $users = \Yii::$app->db->createCommand($sql)->queryAll();

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('MCO GROUP')
            ->setLastModifiedBy('MCO GROUP')
            ->setTitle('Customer Export')
            ->setSubject('Customer Data')
            ->setDescription('Exported customer data from the application');

        // Set column headers
        $headers = [
            'A1' => 'รหัส',
            'B1' => 'ชื่อบริษัท',
            'C1' => 'เลขที่',
            'D1' => 'ถนน',
            'E1' => 'ซอย',
            'F1' => 'ตำบล/แขวง',
            'G1' => 'อําเภอ/เขต',
            'H1' => 'จังหวัด',
            'I1' => 'รหัสไปรษณีย์',
            'J1' => 'Tax ID',
            'K1' => 'สนญ',
            'L1' => 'สาขา',
            'M1' => 'ผู้ติดต่อ',
            'N1' => 'เบอร์โทร',
            'O1' => 'อีเมล',
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

        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(20);

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
            $sheet->setCellValue('A' . $row, $users[$i]['code']);
            $sheet->setCellValue('B' . $row, $users[$i]['name']);
            $sheet->setCellValue('C' . $row, $users[$i]['home_number']);
            $sheet->setCellValue('D' . $row, $users[$i]['street']);
            $sheet->setCellValue('E' . $row, $users[$i]['aisle']);
            $sheet->setCellValue('F' . $row, $users[$i]['district_name']);
            $sheet->setCellValue('G' . $row, $users[$i]['city_name']);
            $sheet->setCellValue('H' . $row, $users[$i]['province_name']);
            $sheet->setCellValue('I' . $row, $users[$i]['zipcode']);
            $sheet->setCellValue('J' . $row, $users[$i]['taxid']);
            $sheet->setCellValue('K' . $row, $users[$i]['is_head'] == 1 ? 'สำนักงานใหญ่' : '');
            $sheet->setCellValue('L' . $row, $users[$i]['branch_name']);
            $sheet->setCellValue('M' . $row, $users[$i]['contact_name']);
            $sheet->setCellValue('N' . $row, $users[$i]['phone']);
            $sheet->setCellValue('O' . $row, $users[$i]['email']);
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
        \Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="customer_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        \Yii::$app->response->headers->add('Cache-Control', 'max-age=0');

        // Write file to output
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $content;
    }
}
