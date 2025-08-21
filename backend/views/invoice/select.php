<?php

use yii\helpers\Html;
use backend\models\Invoice;

/* @var $this yii\web\View */

$this->title = 'เลือกประเภทเอกสาร';
$this->params['breadcrumbs'][] = ['label' => 'จัดการเอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="invoice-select">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-file-invoice"></i> <?= Html::encode($this->title) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="lead text-center mb-4">กรุณาเลือกประเภทเอกสารที่ต้องการสร้าง</p>

                        <div class="row">
                            <!-- ใบแจ้งหนี้ -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card h-100 border-primary shadow-sm invoice-type-card">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-file-alt fa-3x text-primary"></i>
                                        </div>
                                        <h5 class="card-text">ใบแจ้งหนี้</h5>
                                        <p class="card-text">Invoice</p>
                                        <p class="small text-muted">สำหรับแจ้งหนี้</p>
                                        <?= Html::a('<i class="fas fa-plus"></i> สร้างใบแจ้งหนี้',
                                            ['create', 'type' => Invoice::TYPE_QUOTATION],
                                            ['class' => 'btn btn-primary btn-block']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- ใบวางบิล -->
<!--                            <div class="col-md-6 col-lg-3 mb-4">-->
<!--                                <div class="card h-100 border-info shadow-sm invoice-type-card">-->
<!--                                    <div class="card-body text-center">-->
<!--                                        <div class="mb-3">-->
<!--                                            <i class="fas fa-file-invoice fa-3x text-info"></i>-->
<!--                                        </div>-->
<!--                                        <h5 class="card-text">ใบวางบิล</h5>-->
<!--                                        <p class="card-text">Bill Placement</p>-->
<!--                                        <p class="small text-muted">สำหรับวางบิลชำระเงิน</p>-->
<!--                                        --><?php //= Html::a('<i class="fas fa-plus"></i> สร้างใบวางบิล',
//                                            ['create', 'type' => Invoice::TYPE_BILL_PLACEMENT],
//                                            ['class' => 'btn btn-info btn-block']) ?>
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->

                            <!-- ใบกำกับภาษี -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card h-100 border-success shadow-sm invoice-type-card">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-success"></i>
                                        </div>
                                        <h5 class="card-text">ใบกำกับภาษี</h5>
                                        <p class="card-text">Tax Invoice</p>
                                        <p class="small text-muted">สำหรับออกใบกำกับภาษี</p>
                                        <?= Html::a('<i class="fas fa-plus"></i> สร้างใบกำกับภาษี',
                                            ['create', 'type' => Invoice::TYPE_TAX_INVOICE],
                                            ['class' => 'btn btn-success btn-block']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- ใบเสร็จรับเงิน -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card h-100 border-warning shadow-sm invoice-type-card">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-receipt fa-3x text-warning"></i>
                                        </div>
                                        <h5 class="card-text">ใบเสร็จรับเงิน</h5>
                                        <p class="card-text">Receipt</p>
                                        <p class="small text-muted">สำหรับออกใบเสร็จรับเงิน</p>
                                        <?= Html::a('<i class="fas fa-plus"></i> สร้างใบเสร็จ',
                                            ['create', 'type' => Invoice::TYPE_RECEIPT],
                                            ['class' => 'btn btn-warning btn-block text-dark']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Copy from existing invoice -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-copy"></i> สร้างจากเอกสารเดิม
                                        </h6>
                                        <p class="card-text small">คุณสามารถสร้างเอกสารใหม่โดยคัดลอกข้อมูลจากเอกสารที่มีอยู่</p>
                                        <?= Html::a('<i class="fas fa-search"></i> เลือกเอกสารต้นฉบับ',
                                            ['index'],
                                            ['class' => 'btn btn-outline-secondary']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <?= Html::a('<i class="fas fa-list"></i> ดูรายการเอกสารทั้งหมด',
                                ['index'],
                                ['class' => 'btn btn-outline-primary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$this->registerCss("
.invoice-type-card {
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.invoice-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.card-body {
    padding: 2rem 1rem;
}

.fa-3x {
    margin-bottom: 1rem;
}

.btn-block {
    margin-top: 1rem;
}
");
?>