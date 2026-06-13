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
                $model->code = Vendor::getlastno();
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
                    
                    $companyId = \Yii::$app->session->get('company_id');
                    
                    $mismatchedVendors = [];
                    $newVendors = [];

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
                            $existingVendor = Vendor::find()->where(['name' => $name, 'company_id' => $companyId])->one();
                            if ($existingVendor) {
                                $dbTaxId = trim($existingVendor->taxid . "");
                                if ($dbTaxId !== $taxId) {
                                    $mismatchedVendors[] = [
                                        'id' => $existingVendor->id,
                                        'name' => $name,
                                        'old_taxid' => $dbTaxId,
                                        'new_taxid' => $taxId,
                                        'address' => $addressStr,
                                        'old_address' => $existingVendor->full_address,
                                    ];
                                }
                            } else {
                                $newVendors[] = [
                                    'name' => $name,
                                    'address' => $addressStr,
                                    'taxid' => $taxId,
                                ];
                            }
                        }
                    }

                    unlink($filePath); // Delete file after processing

                    return $this->render('import-preview', [
                        'mismatchedVendors' => $mismatchedVendors,
                        'newVendors' => $newVendors,
                    ]);

                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการอ่านไฟล์: ' . $e->getMessage());
                }
            }
        }
        return $this->redirect(['index']);
    }

    public function actionImportConfirm()
    {
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $mismatchedVendors = $request->post('mismatchedVendors', []);
            $newVendors = $request->post('newVendors', []);
            
            $companyId = \Yii::$app->session->get('company_id');
            $updatedCount = 0;
            $newCount = 0;
            
            // Update mismatched vendors
            if (is_array($mismatchedVendors)) {
                foreach ($mismatchedVendors as $v) {
                    $model = Vendor::findOne(['id' => $v['id'], 'company_id' => $companyId]);
                    if ($model) {
                        $model->taxid = $v['new_taxid'] ?? '';
                        $model->full_address = $v['address'] ?? '';
                        if ($model->save()) {
                            // Update address info
                            if (!empty($v['address'])) {
                                $model_address = \common\models\AddressInfo::find()->where(['party_id' => $model->id, 'party_type_id' => 1])->one();
                                if ($model_address) {
                                    $model_address->address = $v['address'];
                                    $model_address->save(false);
                                } else {
                                    $model_address = new \common\models\AddressInfo();
                                    $model_address->party_id = $model->id;
                                    $model_address->party_type_id = 1;
                                    $model_address->address = $v['address'];
                                    $model_address->save(false);
                                }
                            }
                            $updatedCount++;
                        }
                    }
                }
            }
            
            // Insert new vendors
            if (is_array($newVendors)) {
                foreach ($newVendors as $v) {
                    $model = new Vendor();
                    $model->code = Vendor::getlastno();
                    $model->name = $v['name'] ?? '';
                    $model->taxid = $v['taxid'] ?? '';
                    $model->full_address = $v['address'] ?? '';
                    $model->status = 1;
                    $model->company_id = $companyId;
                    $model->vendor_group_id = 0;
                    
                    if ($model->save()) {
                        if (!empty($v['address'])) {
                            $model_address = new \common\models\AddressInfo();
                            $model_address->party_id = $model->id;
                            $model_address->party_type_id = 1;
                            $model_address->address = $v['address'];
                            $model_address->save(false);
                        }
                        $newCount++;
                    }
                }
            }
            
            \Yii::$app->session->setFlash('success', "นำเข้าข้อมูลสำเร็จ! อัพเดทข้อมูล $updatedCount รายการ และสร้างใหม่ $newCount รายการ");
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

    public function actionExportExpress()
    {
        $sql = "SELECT * FROM vendor ORDER BY id ASC";
        $vendors = \Yii::$app->db->createCommand($sql)->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Row 1: Instruction
        $sheet->mergeCells('A1:T1');
        $sheet->setCellValue('A1', '**ช่องข้อความที่เป็นอักษรสีแดง ต้องใช้ให้ครบทุกช่อง**');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000FF'));

        // Row 2: Headers
        $headers = [
            'SUPCOD', 'PRENAM', 'SUPNAM', 'ADDR01', 'ADDR02', 'ADDR03', 'ZIPCOD', 'TELNUM', 'CONTACT',
            'SUPTYP', 'ACCNUM', 'FLGVAT', 'VATRAT', 'PAYTRM', 'PAYCOND', 'DISC', 'CRLINE', 'REMARK', 'TAXID', 'ORGNUM'
        ];

        $mandatoryCols = ['A', 'B', 'C', 'D', 'L', 'M', 'S']; // SUPCOD, PRENAM, SUPNAM, ADDR01, FLGVAT, VATRAT, TAXID

        foreach ($headers as $index => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($col . '2', $header);
            if (in_array($col, $mandatoryCols)) {
                $sheet->getStyle($col . '2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFF0000'));
            }
        }

        // Row 3: Thai Labels
        $labels = [
            '*รหัสผู้จำหน่าย*', '*คำนำหน้าชื่อ*', '*ชื่อผู้จำหน่าย*', '*ที่อยู่บรรทัดที่ 1*', 'ที่อยู่บรรทัดที่ 2', 'ที่อยู่บรรทัดที่ 3', 'รหัสไปรษณีย์', 'โทร-Fax', 'ชื่อผู้ติดต่อ',
            '*ประเภทผู้จำหน่าย*', '*เลขที่บัญชี*', '*ประเภทราคา*', '*ภาษีมูลค่าเพิ่ม*', 'เครดิต', 'เงื่อนไขการชำระเงิน', 'ส่วนลด', 'วงเงินสินเชื่อ', 'หมายเหตุ', '*เลขประจำตัวผู้เสียภาษี*', '*สาขา*'
        ];
        foreach ($labels as $index => $label) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($col . '3', $label);
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if (in_array($col, $mandatoryCols)) {
                $sheet->getStyle($col . '3')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFF0000'));
            }
        }

        // Row 4: Constraints
        $constraints = [
            'ห้ามเกิน 10 ตัว ห้ามเว้นวรรค,ห้ามใช้เครื่องหมาย / * " .', 'ห้ามเกิน 15 ตัว', 'ห้ามเกิน 60 ตัว', 'ห้ามเกิน 50 ตัว', 'ห้ามเกิน 50 ตัว', 'ห้ามเกิน 20 ตัว', 'ห้ามเกิน 5 ตัว', 'ห้ามเกิน 50 ตัว', 'ห้ามเกิน 40 ตัว',
            'ห้ามเกิน 2 ตัว เช่น 00=ผู้จำหน่ายประจำ,04=ผู้จำหน่ายชั่วคราว', 'ห้ามเกิน 15 ตัว', '0=ไม่มีvat,1=รวมvat,2=แยกvat', '0, 7', 'ห้ามเกิน 3 ตัว', 'ห้ามเกิน 25 ตัว', 'ห้ามเกิน 10 ตัว', 'ห้ามเกิน 8 ตัว', 'ห้ามเกิน 60 ตัว', 'ห้ามเกิน 15 ตัว', 'สำนักงานใหญ่กรอก 0 สาขา เช่น สาขาที่ 00011 ให้กรอก 11 เท่านั้น'
        ];
        foreach ($constraints as $index => $constraint) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($col . '4', $constraint);
            $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . '4')->getFont()->setSize(8);
            if (in_array($col, $mandatoryCols)) {
                $sheet->getStyle($col . '4')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFF0000'));
            }
        }

        // Row 6: Note (Blue)
        $sheet->mergeCells('A6:C6');
        $sheet->setCellValue('A6', '**กรณีตัวอักษรเป็นภาษาอังกฤษ จะต้องใช้อักษรตัวใหญ่**');
        $sheet->getStyle('A6')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000FF'));

        // Fill Data starting from Row 7
        $rowNum = 7;
        foreach ($vendors as $v) {
            $sheet->setCellValue('A' . $rowNum, strtoupper($v['code']));
            
            // PRENAM and SUPNAM logic
            $name = $v['name'];
            $prenam = '';
            $supnam = $name;
            $prefixes = ['บจก.', 'หจก.', 'บริษัท', 'ห้างหุ้นส่วนจำกัด', 'นาย', 'นาง', 'นางสาว'];
            foreach ($prefixes as $p) {
                if (mb_strpos($name, $p) === 0) {
                    $prenam = $p;
                    $supnam = trim(mb_substr($name, mb_strlen($p)));
                    break;
                }
            }
            if ($prenam == '') $prenam = '.'; // Default for mandatory field

            $sheet->setCellValue('B' . $rowNum, $prenam);
            $sheet->setCellValue('C' . $rowNum, strtoupper($supnam));
            
            // Address logic
            $addr1 = trim(($v['home_number'] ?? '') . ' ' . ($v['street'] ?? ''));
            $addr2 = trim(($v['aisle'] ?? '') . ' ' . ($v['district_name'] ?? ''));
            $addr3 = trim(($v['city_name'] ?? '') . ' ' . ($v['province_name'] ?? ''));
            
            $sheet->setCellValue('D' . $rowNum, strtoupper($addr1));
            $sheet->setCellValue('E' . $rowNum, strtoupper($addr2));
            $sheet->setCellValue('F' . $rowNum, strtoupper($addr3));
            $sheet->setCellValue('G' . $rowNum, $v['zipcode']);
            $sheet->setCellValue('H' . $rowNum, $v['phone']);
            $sheet->setCellValue('I' . $rowNum, strtoupper($v['contact_name']));
            
            $sheet->setCellValue('J' . $rowNum, '00'); // SUPTYP
            $sheet->setCellValue('K' . $rowNum, ''); // ACCNUM
            $sheet->setCellValue('L' . $rowNum, '1'); // FLGVAT (Default to 1 - Include)
            $sheet->setCellValue('M' . $rowNum, '7'); // VATRAT
            $sheet->setCellValue('N' . $rowNum, '0'); // PAYTRM
            $sheet->setCellValue('O' . $rowNum, ''); // PAYCOND
            $sheet->setCellValue('P' . $rowNum, ''); // DISC
            $sheet->setCellValue('Q' . $rowNum, '0'); // CRLINE
            $sheet->setCellValue('R' . $rowNum, strtoupper($v['description'])); // REMARK
            $sheet->setCellValue('S' . $rowNum, $v['taxid']);
            
            $orgnum = '0';
            if (isset($v['is_head']) && $v['is_head'] == 0 && !empty($v['branch_name'])) {
                $orgnum = preg_replace('/[^0-9]/', '', $v['branch_name']);
                if (empty($orgnum)) $orgnum = '0';
                else $orgnum = (int)$orgnum;
            }
            $sheet->setCellValue('T' . $rowNum, $orgnum);
            
            $rowNum++;
        }

        // Auto width for columns
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set response headers for download
        \Yii::$app->response->format = Response::FORMAT_RAW;
        \Yii::$app->response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        \Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="vendor_express_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        \Yii::$app->response->headers->add('Cache-Control', 'max-age=0');

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
