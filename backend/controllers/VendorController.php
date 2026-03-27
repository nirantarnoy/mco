<?php

namespace backend\controllers;

use backend\models\UnitSearch;
use backend\models\Vendor;
use backend\models\VendorSearch;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * VendorController implements the CRUD actions for Vendor model.
 */
class VendorController extends BaseController
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
     * Lists all Vendor models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $pageSize = \Yii::$app->request->post("perpage") ?: \Yii::$app->request->get("perpage") ?: 20;
        $searchModel = new VendorSearch();
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
     * Displays a single Vendor model.
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
     * Creates a new Vendor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Vendor();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $address = \Yii::$app->request->post('cus_address');
                $street = \Yii::$app->request->post('cus_street');
                $district_id = \Yii::$app->request->post('district_id');
                $city_id = \Yii::$app->request->post('city_id');
                $province_id = \Yii::$app->request->post('province_id');
                $zipcode = \Yii::$app->request->post('zipcode');

                $party_type_id = 1;
              //  $model->code = $model::getLastno();
                $model->vendor_group_id = 0;
                if($model->save()){
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
                }else{
                    \Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . json_encode($model->getErrors()));
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
     * Updates an existing Vendor model.
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

            $party_type_id = 1;

            if($model->save()){
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
     * Deletes an existing Vendor model.
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
     * Finds the Vendor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Vendor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Vendor::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public
    function actionShowcity($id)
    {
        $model = \common\models\Amphur::find()->where(['PROVINCE_ID' => $id])->all();

        if (count($model) > 0) {
            echo "<option>--- เลือกอำเภอ ---</option>";
            foreach ($model as $value) {

                echo "<option value='" . $value->AMPHUR_ID . "'>$value->AMPHUR_NAME</option>";

            }
        } else {
            echo "<option>-</option>";
        }
    }

    public
    function actionShowdistrict($id)
    {
        $model = \common\models\District::find()->where(['AMPHUR_ID' => $id])->all();

        if (count($model) > 0) {
            foreach ($model as $value) {

                echo "<option value='" . $value->DISTRICT_ID . "'>$value->DISTRICT_NAME</option>";

            }
        } else {
            echo "<option>-</option>";
        }
    }

    public function actionShowzipcode($id)
    {
        $model = \common\models\Amphur::find()->where(['AMPHUR_ID' => $id])->one();
//        echo $id;
        if ($model) {
            echo $model->POSTCODE;
//            echo '1110';
        } else {
            echo "";
        }
//        echo '111';
    }

    public function actionImportvendor()
    {
        $uploaded = UploadedFile::getInstanceByName('file_vendor');
        if (!empty($uploaded)) {
            $path = '../web/uploads/files/vendors/';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $fileName = time() . "." . $uploaded->getExtension();
            $filePath = $path . $fileName;

            if ($uploaded->saveAs($filePath)) {
                try {
                    ini_set('memory_limit', '1024M'); // Increase memory limit
                    
                    $reader = IOFactory::createReaderForFile($filePath);
                    $reader->setReadDataOnly(true); // Read only data (skip styles)
                    $spreadsheet = $reader->load($filePath);
                    
                    $res = 0;
                    $dup = 0;
                    $companyId = \Yii::$app->session->get('company_id');

                    // Loop through all sheets
                    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                        foreach ($worksheet->getRowIterator() as $index => $row) {
                            if ($index == 1) continue; // Skip header

                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false); // Enable for empty cells
                            
                            $rowData = [];
                            foreach ($cellIterator as $cell) {
                                $rowData[] = $cell->getValue();
                            }

                            if (empty($rowData[0])) continue; // Skip empty names

                            $name = trim($rowData[0] . "");
                            $addressStr = isset($rowData[1]) ? trim($rowData[1] . "") : '';
                            $taxId = isset($rowData[2]) ? trim($rowData[2] . "") : '';

                            // Check duplicate name for this company
                            $exists = Vendor::find()->where(['name' => $name, 'company_id' => $companyId])->exists();
                            if ($exists) {
                                $dup++;
                                continue;
                            }

                            $model = new Vendor();
                            $model->code = Vendor::getlastno();
                            $model->name = $name;
                            $model->taxid = $taxId;
                            $model->full_address = $addressStr; // Use description for full address if needed
                            $model->status = 1;
                            $model->company_id = $companyId;

                            if ($model->save()) {
                                // Create address entry
                                if (!empty($addressStr)) {
                                    $model_address = new \common\models\AddressInfo();
                                    $model_address->party_id = $model->id;
                                    $model_address->party_type_id = 1; // Vendor type
                                    $model_address->address = $addressStr;
                                    $model_address->save(false);
                                }
                                $res++;
                            }
                        }
                    }

                    unlink($filePath); // Delete file after processing

                    if ($res > 0) {
                        \Yii::$app->session->setFlash('success', "นำเข้าข้อมูลสำเร็จ $res รายการ" . ($dup > 0 ? " (ข้ามรายการซ้ำ $dup รายการ)" : ""));
                    } else {
                        \Yii::$app->session->setFlash('warning', "ไม่มีข้อมูลใหม่ถูกนำเข้า" . ($dup > 0 ? " ($dup รายการซ้ำ)" : ""));
                    }

                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการอ่านไฟล์: ' . $e->getMessage());
                }
            }
        }
        return $this->redirect(['index']);
    }
    public function actionExportVendors()
    {
        // Get data from your model
        // $users = Product::find()->joinWith('StockSum')->all();

        $users = null;
        $sql = "SELECT * FROM vendor ORDER BY id ASC";
        $users = \Yii::$app->db->createCommand($sql)->queryAll();

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('MCO GROUP')
            ->setLastModifiedBy('MCO GROUP')
            ->setTitle('Vendor Export')
            ->setSubject('Vendor Data')
            ->setDescription('Exported vendor data from the application');

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
        \Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="vendor_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        \Yii::$app->response->headers->add('Cache-Control', 'max-age=0');

        // Write file to output
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $content;
    }
    public function actionAddDocFile(){
        $id = \Yii::$app->request->post('id');
        if($id){
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                // Create directory if not exists
                $path = 'uploads/vendor_doc/';
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "vendor_" . time()."_".$loop . "." . $file->getExtension();
                    if ($file->saveAs($path . $upfiles)) {
                        $model_doc = new \common\models\VendorDoc();
                        $model_doc->vendor_id = $id;
                        $model_doc->doc_name = $upfiles;
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

    public function actionDeleteDocFile(){
        $id = \Yii::$app->request->post('id');
        $doc_delete_list = trim(\Yii::$app->request->post('doc_delete_list'));
        if($id){
            $model_doc = \common\models\VendorDoc::find()->where(['vendor_id' => $id,'doc_name' => $doc_delete_list])->one();
            if($model_doc){
                if($model_doc->delete()){
                    if(file_exists('uploads/vendor_doc/'.$model_doc->doc_name)){
                        unlink('uploads/vendor_doc/'.$model_doc->doc_name);
                    }
                }
            }
        }
        return $this->redirect(['update', 'id' => $id]);
    }
}
