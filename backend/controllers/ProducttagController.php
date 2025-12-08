<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;

class ProducttagController extends BaseController
{
    // Sample product data
    private function getProducts()
    {
        return [
            ['id' => 1, 'ref_po' => 'PO001', 'description' => 'แอร์ Daikin Inverter 12000 BTU', 'model' => 'FTKF35UV2S', 'brand' => 'DAIKIN', 'quantity' => 10],
            ['id' => 2, 'ref_po' => 'PO002', 'description' => 'แอร์ Mitsubishi Heavy 18000 BTU', 'model' => 'SRK18CSS-S3', 'brand' => 'MITSUBISHI', 'quantity' => 5],
            ['id' => 3, 'ref_po' => 'PO003', 'description' => 'แอร์ LG Dual Inverter 9000 BTU', 'model' => 'IG09R.SE', 'brand' => 'LG', 'quantity' => 8],
            ['id' => 4, 'ref_po' => 'PO004', 'description' => 'แอร์ Samsung Wind-Free 24000 BTU', 'model' => 'AR24TYHZFWKN', 'brand' => 'SAMSUNG', 'quantity' => 3],
            ['id' => 5, 'ref_po' => 'PO005', 'description' => 'แอร์ Panasonic Standard 13000 BTU', 'model' => 'CS-PU13WKT', 'brand' => 'PANASONIC', 'quantity' => 12],
        ];
    }

    public function actionIndex()
    {
        $products = $this->getProducts();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $products,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPrintTag()
    {
        $selectedIds = Yii::$app->request->post('selection');

        if (empty($selectedIds)) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกสินค้าที่ต้องการพิมพ์');
            return $this->redirect(['index']);
        }

        $products = $this->getProducts();
        $selectedProducts = [];

        foreach ($products as $product) {
            if (in_array($product['id'], $selectedIds)) {
                $selectedProducts[] = $product;
            }
        }

        return $this->render('print-tag', [
            'selectedProducts' => $selectedProducts,
        ]);
    }

    public function actionGenerateTags()
    {
        $productData = Yii::$app->request->post('products');
        $printData = [];

        if ($productData) {
            foreach ($productData as $data) {
                $product = json_decode($data['product'], true);
                $copies = (int)$data['copies'];

                for ($i = 0; $i < $copies; $i++) {
                    $printData[] = $product;
                }
            }
        }

        $format = Yii::$app->request->get('format', 'html');

        if ($format === 'pdf') {
            $content = $this->renderPartial('print-preview', ['printData' => $printData]);

            $pdf = new \kartik\mpdf\Pdf([
                'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
                'format' => \kartik\mpdf\Pdf::FORMAT_A4,
                'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
                'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
                'content' => $content,
                'cssFile' => '@frontend/web/css/print-tag.css',
                'options' => ['title' => 'Product Tags'],
                'methods' => [
                    'SetHeader' => ['Product Tags'],
                    'SetFooter' => ['{PAGENO}'],
                ]
            ]);

            return $pdf->render();
        } elseif ($format === 'excel') {
            return $this->exportExcel($printData);
        } else {
            return $this->renderPartial('print-preview', ['printData' => $printData]);
        }
    }

    private function exportExcel($printData)
    {
        $objPHPExcel = new \PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();

        $row = 1;
        $col = 0;

        foreach ($printData as $index => $product) {
            if ($col >= 3) {
                $col = 0;
                $row += 5;
            }

            $startCol = $col * 3;

            $sheet->setCellValueByColumnAndRow($startCol, $row, 'Ref.Po: ' . $product['ref_po']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 1, 'Descrip: ' . $product['description']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 2, 'Model: ' . $product['model']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 3, 'Brand: ' . $product['brand']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 4, 'Q\'ty: ' . $product['quantity']);

            $col++;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="product_tags.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        Yii::$app->end();
    }

    public function actionCreate()
    {
        // Create action implementation
        return $this->render('create');
    }

    public function actionUpdate($id)
    {
        // Update action implementation
        return $this->render('update', ['id' => $id]);
    }

    public function actionDelete($id)
    {
        // Delete action implementation
        Yii::$app->session->setFlash('success', 'ลบข้อมูลสำเร็จ');
        return $this->redirect(['index']);
    }

    public function actionView($id)
    {
        // View action implementation
        return $this->render('view', ['id' => $id]);
    }
}
