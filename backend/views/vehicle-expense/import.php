<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'นำเข้าข้อมูลค่าใช้จ่ายรถยนต์';
$this->params['breadcrumbs'][] = ['label' => 'ค่าใช้จ่ายรถยนต์', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;

// ดึง error details จาก session
$importErrors = Yii::$app->session->get('import_errors');
if ($importErrors) {
    Yii::$app->session->remove('import_errors');
}
?>

<div class="vehicle-expense-import">

    <!-- แสดง Error Details ถ้ามี -->
    <?php if (!empty($importErrors)): ?>
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i>
                    รายละเอียดข้อผิดพลาดจากการ Import (แสดง 10 แถวแรก)
                </h3>
            </div>
            <div class="box-body">
                <?php foreach ($importErrors as $error): ?>
                    <div class="callout callout-danger">
                        <h4>แถวที่ <?= $error['row'] ?></h4>

                        <div class="row">
                            <div class="col-md-6">
                                <strong>ข้อมูลต้นฉบับ:</strong>
                                <pre><?= Html::encode(json_encode($error['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                            <div class="col-md-6">
                                <strong>ข้อมูลที่แปลงแล้ว:</strong>
                                <pre><?= Html::encode(json_encode($error['parsed'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                        </div>

                        <strong class="text-danger">Validation Errors:</strong>
                        <ul>
                            <?php foreach ($error['errors'] as $field => $messages): ?>
                                <li>
                                    <strong><?= $field ?>:</strong>
                                    <?= implode(', ', $messages) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="box box-primary">
        <div class="box-body">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <strong>คำแนะนำ:</strong>
                <ul class="mb-0">
                    <li><strong class="text-danger">ไฟล์ต้องเป็นนามสกุล .csv เท่านั้น</strong></li>
                    <li>ข้อมูลต้องมีคอลัมน์ตามลำดับ: <strong>วันที่ใช้งานรถ | Job no. | ทะเบียนรถ | ระยะทางรวม (กม) |
                            ค่าใช้จ่ายรถ | จำนวนผู้ใช้รถ | ค่าจ้างรวม</strong></li>
                    <li>Job No ต้องเป็นรูปแบบ <code>RY-QTXX-XXXXXX</code></li>
                    <li>วันที่ใช้รูปแบบ DD/MM/YYYY (เช่น 23/07/2023)</li>
                    <li>ขนาดไฟล์ไม่เกิน 10 MB</li>
                    <li><strong>หากแถวใดไม่มีวันที่ ระบบจะใช้วันที่จากแถวก่อนหน้า</strong></li>
                    <li><strong>แถวที่มีคำว่า "รวม" จะถูกข้ามอัตโนมัติ</strong></li>
                </ul>
            </div>

            <?php $form = ActiveForm::begin([
                'options' => ['enctype' => 'multipart/form-data', 'class' => 'form-horizontal'],
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-sm-8\">{input}\n{error}</div>",
                    'labelOptions' => ['class' => 'col-sm-2 control-label'],
                ],
            ]); ?>

            <?= $form->field($model, 'file')->fileInput([
                'accept' => '.csv',
                'class' => 'form-control',
            ])->label('เลือกไฟล์ CSV') ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?= Html::submitButton('<i class="fa fa-upload"></i> นำเข้าข้อมูล', [
                        'class' => 'btn btn-primary btn-lg',
                    ]) ?>
                    <?= Html::a('<i class="fa fa-download"></i> ดาวน์โหลด Template (Excel)', ['download-template'], [
                        'class' => 'btn btn-success',
                    ]) ?>
                    <?= Html::a('<i class="fa fa-file-text-o"></i> ดาวน์โหลด Template (CSV)', ['download-csv-template'], [
                        'class' => 'btn btn-info',
                    ]) ?>
                    <?= Html::a('<i class="fa fa-list"></i> ดูรายการข้อมูล', ['list'], [
                        'class' => 'btn btn-default',
                    ]) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- วิธีแปลงไฟล์ Excel เป็น CSV -->
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-lightbulb-o"></i>
                วิธีแปลงไฟล์ Excel เป็น CSV
            </h3>
        </div>
        <div class="box-body">
            <ol>
                <li>ดาวน์โหลด <strong>Template Excel</strong> ข้างบน</li>
                <li>กรอกข้อมูลลงใน Excel</li>
                <li>คลิก <strong>File → Save As</strong></li>
                <li>เลือก <strong>Save as type: CSV (Comma delimited) (*.csv)</strong></li>
                <li>คลิก Save</li>
                <li>Excel อาจถามว่า "Do you want to keep that format?" ให้คลิก <strong>Yes</strong></li>
                <li>นำไฟล์ .csv ที่บันทึกมา Import ที่นี่</li>
            </ol>
        </div>
    </div>

    <!-- ตัวอย่างรูปแบบข้อมูล -->
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-table"></i> ตัวอย่างรูปแบบข้อมูล</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="bg-light-blue">
                <tr>
                    <th class="text-center">วันที่ใช้งานรถ</th>
                    <th class="text-center">Job no.<br><small>(RY-QTXX-XXXXXX)</small></th>
                    <th class="text-center">ทะเบียนรถ</th>
                    <th class="text-center">ระยะทางรวม<br>(กม)</th>
                    <th class="text-center">ค่าใช้จ่ายรถ<br>(บาท)</th>
                    <th class="text-center">จำนวนผู้ใช้รถ</th>
                    <th class="text-center">ค่าจ้างรวม<br>(บาท)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-center">23/07/2023</td>
                    <td>RY-QT23-000007</td>
                    <td class="text-center">บล 1057</td>
                    <td class="text-right">76</td>
                    <td class="text-right">1,300</td>
                    <td class="text-center">2</td>
                    <td class="text-right">4,000</td>
                </tr>
                <tr>
                    <td class="text-center"><em class="text-muted">(ว่าง - ใช้วันที่เดียวกับแถวบน)</em></td>
                    <td>RY-QT25-000024</td>
                    <td class="text-center">กฉ 2432</td>
                    <td class="text-right">80</td>
                    <td class="text-right">400</td>
                    <td class="text-center">1</td>
                    <td class="text-right">0</td>
                </tr>
                <tr class="bg-warning">
                    <td class="text-center"><strong>23/07/2023 รวม</strong></td>
                    <td colspan="6" class="text-center"><em>(แถวนี้จะถูกข้ามอัตโนมัติ)</em></td>
                </tr>
                <tr>
                    <td class="text-center">25/05/2025</td>
                    <td>RY-QT25-000103</td>
                    <td class="text-center">บล 1057</td>
                    <td class="text-right">65</td>
                    <td class="text-right">0</td>
                    <td class="text-center">1</td>
                    <td class="text-right">0</td>
                </tr>
                </tbody>
            </table>

            <div class="alert alert-warning mt-3">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>ข้อควรระวัง:</strong>
                <ul class="mb-0">
                    <li>ไฟล์ CSV ต้องเป็น UTF-8 encoding</li>
                    <li>ถ้าเปิดไฟล์ CSV ด้วย Excel แล้วตัวอักษรไทยเพี้ยน ไม่ต้องกังวล ให้ Save As เป็น CSV อีกครั้ง</li>
                    <li>ไม่ควรใช้ตัวคั่นพิเศษ เช่น ; หรือ | ในข้อมูล</li>
                </ul>
            </div>
        </div>
    </div>