<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\StockCardSearch */
/* @var $results array */

$this->title = 'รายงานสต็อกการ์ด (Stock Card Report)';
$this->params['breadcrumbs'][] = $this->title;

$company_id = Yii::$app->session->get('company_id');

// Style to match the screenshot colors
$this->registerCss("
    .report-table thead th {
        vertical-align: middle !important;
        text-align: center;
        border: 1px solid #dee2e6;
        font-weight: bold;
    }
    .report-table td {
        border: 1px solid #dee2e6;
        padding: 5px 8px;
    }
    .header-purch { background-color: #28a745 !important; color: #ffffff !important; }
    .header-sales { background-color: #ffc107 !important; color: #212529 !important; }
    .header-balance { background-color: #007bff !important; color: #ffffff !important; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
");
?>

<div class="stock-card-report">
    <div class="card mb-4">
        <div class="card-header">
            <h4><i class="fas fa-file-invoice"></i> <?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['stock-card'],
            ]); ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($searchModel, 'product_id')->widget(Select2::class, [
                        'data' => $searchModel->getProductList(),
                        'options' => ['placeholder' => '--- เลือกสินค้า ---'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'from_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'จากวันที่'],
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'autoclose' => true,
                            'todayHighlight' => true
                        ]
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'to_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'ถึงวันที่'],
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'autoclose' => true,
                            'todayHighlight' => true
                        ]
                    ]) ?>
                </div>
                <div class="col-md-2" style="padding-top: 30px;">
                    <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="fas fa-file-excel"></i> Export', array_merge(['export-excel'], Yii::$app->request->queryParams), ['class' => 'btn btn-success']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if (!empty($results)): ?>
        <div class="card">
            <div class="card-body p-0" style="overflow-x: auto;">
                <table class="table table-bordered table-striped report-table mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 150px;">สินค้า</th>
                            <th rowspan="2" style="width: 100px;">วันที่ทำรายการ</th>
                            <th rowspan="2" style="width: 80px;">หน่วย</th>
                            
                            <!-- Green Section: Inbound/Purchases -->
                            <th colspan="4" class="header-purch">ซื้อ/รับเข้า</th>
                            
                            <!-- Yellow Section: Outbound/Sales -->
                            <th rowspan="2" class="header-sales">Ref No.</th>
                            <th colspan="4" class="header-sales">ขาย/จ่ายออก</th>
                            
                            <th rowspan="2" class="header-sales">ราคาต่อหน่วย</th>
                            <th rowspan="2" class="header-sales">รวมขาย/ของแถม</th>
                            
                            <th rowspan="2" class="header-balance">คงเหลือ (ชิ้น)</th>
                            <th rowspan="2">หมายเหตุ</th>
                        </tr>
                        <tr>
                            <th class="header-purch">ซื้อ</th>
                            <th class="header-purch">ส่งคืน (Out)</th>
                            <th class="header-purch">ราคาต่อหน่วย</th>
                            <th class="header-purch">รวมซื้อ/ส่งคืน</th>
                            
                            <th class="header-sales">ขาย</th>
                            <th class="header-sales">ของแถม</th>
                            <th class="header-sales">รับคืน (In)</th>
                            <th class="header-sales">รับคืนของแถม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $runningBalance = $results['initialBalance'];
                        if ($runningBalance != 0): ?>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="3" class="text-right">ยอดยกมา:</td>
                                <td colspan="11"></td>
                                <td class="text-right"><?= number_format($runningBalance, 2) ?></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($results['transactions'] as $trans): 
                            $unitPrice = ($trans->qty != 0) ? ($trans->line_price / $trans->qty) : 0;
                            
                            // Initialize columns
                            $colBuy = ''; $colReturnPurch = ''; $colPurchPrice = ''; $colPurchTotal = '';
                            $colRef = $trans->journalTrans->journal_no ?? '-';
                            $colSale = ''; $colFree = ''; $colReturnSale = ''; $colReturnFree = ''; $colSalePrice = ''; $colSaleTotal = '';
                            
                            $isIncoming = ($trans->stock_type_id == 1);
                            
                            // Determine logic based on trans_type_id
                            // 1: PO Receive, 2: Cancel PO Receive, 3: Issue, 4: Return Issue
                            switch ($trans->trans_type_id) {
                                case 1: // Purchase (In)
                                    $colBuy = $trans->qty;
                                    $colPurchPrice = $unitPrice;
                                    $colPurchTotal = $trans->line_price;
                                    $runningBalance += $trans->qty;
                                    break;
                                case 2: // Return Purchase (Out)
                                    $colReturnPurch = $trans->qty;
                                    $colPurchPrice = $unitPrice;
                                    $colPurchTotal = $trans->line_price;
                                    $runningBalance -= $trans->qty;
                                    break;
                                case 3: // Issue/Sale (Out)
                                    if ($unitPrice == 0) {
                                        $colFree = $trans->qty;
                                    } else {
                                        $colSale = $trans->qty;
                                    }
                                    $colSalePrice = $unitPrice;
                                    $colSaleTotal = $trans->line_price;
                                    $runningBalance -= $trans->qty;
                                    break;
                                case 4: // Return Issue/Sale (In)
                                    if ($unitPrice == 0) {
                                        $colReturnFree = $trans->qty;
                                    } else {
                                        $colReturnSale = $trans->qty;
                                    }
                                    $colSalePrice = $unitPrice;
                                    $colSaleTotal = $trans->line_price;
                                    $runningBalance += $trans->qty;
                                    break;
                                case 8: // Adjust Stock
                                    if ($isIncoming) {
                                        $colBuy = $trans->qty;
                                        $runningBalance += $trans->qty;
                                    } else {
                                        $colSale = $trans->qty;
                                        $runningBalance -= $trans->qty;
                                    }
                                    break;
                            }
                        ?>
                            <tr>
                                <td><?= $trans->product ? Html::encode($trans->product->code . ' - ' . $trans->product->name) : '-' ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($trans->trans_date)) ?></td>
                                <td class="text-center"><?= $trans->product ? ($trans->product->unit->name ?? 'ชิ้น') : '-' ?></td>
                                
                                <!-- Purch Section -->
                                <td class="text-right"><?= $colBuy ?></td>
                                <td class="text-right"><?= $colReturnPurch ?></td>
                                <td class="text-right"><?= $colPurchPrice !== '' ? number_format($colPurchPrice, 2) : '' ?></td>
                                <td class="text-right"><?= $colPurchTotal !== '' ? number_format($colPurchTotal, 2) : '' ?></td>
                                
                                <!-- Sales Section -->
                                <td class="text-center"><?= Html::encode($colRef) ?></td>
                                <td class="text-right"><?= $colSale ?></td>
                                <td class="text-right"><?= $colFree ?></td>
                                <td class="text-right"><?= $colReturnSale ?></td>
                                <td class="text-right"><?= $colReturnFree ?></td>
                                <td class="text-right"><?= $colSalePrice !== '' ? number_format($colSalePrice, 2) : '' ?></td>
                                <td class="text-right"><?= $colSaleTotal !== '' ? number_format($colSaleTotal, 2) : '' ?></td>
                                
                                <!-- Balance Section -->
                                <td class="text-right font-weight-bold" style="background-color: #e9ecef;"><?= number_format($runningBalance, 2) ?></td>
                                <td><?= Html::encode($trans->remark) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted">
                <p class="mb-0 small">* รายงานนี้สรุปความเคลื่อนไหวจากรายการสินค้าที่บันทึกผ่าน Stock Transaction</p>
            </div>
        </div>
    <?php elseif (Yii::$app->request->queryParams): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> ไม่พบข้อมูลความเคลื่อนไหวสต็อกตามเงื่อนไขที่เลือก
        </div>
    <?php endif; ?>
</div>
