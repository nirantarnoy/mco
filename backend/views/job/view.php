<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */

$this->title = $model->job_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="job-view">
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Print Invoice', ['print-invoice', 'id' => $model->id], ['class' => 'btn btn-info','target' => '_blank']) ?>
        <?= Html::a('<i class="fas fa-chart-line"></i> รายงานผู้บริหาร', ['executive-report', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    <?php if(!Yii::$app->user->isGuest && (Yii::$app->user->identity->username == 'mcoadmin' || Yii::$app->user->identity->username == 'sorakrit' || Yii::$app->user->identity->username == 'sirilak')): ?>
        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#mergeJobModal" data-bs-toggle="modal" data-bs-target="#mergeJobModal">
            <i class="fas fa-random"></i> รวมใบงาน
        </button>
    <?php endif; ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //   'id',
            'job_no',
            [
                'attribute' => 'quotation_id',
                'value' => function ($data) {
                    return $data->quotation != null ? $data->quotation->quotation_no:'';
                }
            ],
            [
                'label' => 'ลูกค้า',
                'value' => function ($data) {
                    $cus_data = \backend\models\Quotation::findCustomerData2($data->quotation_id);
                    if(!empty($cus_data)){
                        return $cus_data[0]['customer_name'];
                    }
                   return '';
                }
            ],
            [
                'attribute' => 'job_date',
                'value' => function ($data) {
                    return date('m/d/Y', strtotime($data->job_date));
                }
            ],
            [
                'attribute' => 'start_date',
                'value' => function ($data) {
                    if ($data->start_date != null) {
                        if (date('Y', strtotime($data->start_date)) != 1970) {
                            return date('m/d/Y', strtotime($data->start_date));
                        } else {
                            return '';
                        }
                    }

                }
            ],
            [
                'attribute' => 'end_date',
                'value' => function ($data) {
                    if ($data->start_date != null) {
                        if (date('Y', strtotime($data->end_date)) != 1970) {
                            return date('m/d/Y', strtotime($data->end_date));
                        } else {
                            return '';
                        }
                    }
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return \backend\models\Job::getJobStatusBadge($data->status);
                }
            ],
            [
                'attribute' => 'job_amount',
                'value' => function ($data) {
                    return number_format($data->job_amount, 2);
                }
            ],
            [
                'attribute' => 'created_at',
                'label' => 'วันที่สร้าง',
                'format' => ['datetime', 'php:m/d/Y H:i'],
            ],
            [
                'attribute' => 'created_by',
                'label' => 'สร้างโดย',
                'value' => function ($model) {
                    return \backend\models\User::findEmployeeNameByUserId($model->created_by);
                }
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'วันที่แก้ไข',
                'format' => ['datetime', 'php:m/d/Y H:i'],
            ],
            [
                'attribute' => 'updated_by',
                'label' => 'แก้ไขโดย',
                'value' => function ($model) {
                    return \backend\models\User::findEmployeeNameByUserId($model->updated_by);
                }
            ],
        ],
    ]) ?>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">รายละเอียดสินค้า/บริการ</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>รายการ</th>
                        <th style="width: 100px; text-align: center;">จำนวน</th>
                        <th style="width: 150px; text-align: right;">ราคาต่อหน่วย</th>
                        <th style="width: 150px; text-align: right;">จำนวนเงินรวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $job_lines = \common\models\JobLine::find()->where(['job_id' => $model->id])->all();
                    if ($job_lines): 
                        foreach ($job_lines as $index => $line): ?>
                            <tr>
                                <td style="text-align: center;"><?= $index + 1 ?></td>
                                <td><?= Html::encode($line->product_name ?: \backend\models\Product::findName($line->product_id)) ?></td>
                                <td style="text-align: center;"><?= number_format($line->qty, 2) ?></td>
                                <td style="text-align: right;"><?= number_format($line->line_price, 2) ?></td>
                                <td style="text-align: right; font-weight: bold;"><?= number_format($line->line_total, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #f8f9fa;">
                            <td colspan="4" style="text-align: right; font-weight: bold;">รวมเงินทั้งสิ้น</td>
                            <td style="text-align: right; font-weight: bold; color: #007bff;"><?= number_format($model->job_amount, 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #6c757d;">ไม่มีข้อมูลรายละเอียด</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php if(!Yii::$app->user->isGuest && (Yii::$app->user->identity->username == 'mcoadmin' || Yii::$app->user->identity->username == 'sorakrit' || Yii::$app->user->identity->username == 'sirilak')): ?>
<!-- Modal for Merging Jobs -->
<div class="modal fade" id="mergeJobModal" tabindex="-1" role="dialog" aria-labelledby="mergeJobModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= \yii\helpers\Url::to(['merge-job']) ?>" method="post">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="hidden" name="current_job_id" value="<?= $model->id ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="mergeJobModalLabel">เลือกใบงานที่ต้องการนำมารวม</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>เลือกใบงานที่จะรวมเข้ามา (ใบงานนี้จะถูกยกเลิก)</label>
                        <?php 
                            $jobs = \backend\models\Job::find()
                                ->where(['status' => 1]) // Open only
                                ->andWhere(['!=', 'id', $model->id])
                                ->orderBy(['id' => SORT_DESC])
                                ->all();
                            $listData = \yii\helpers\ArrayHelper::map($jobs, 'id', 'job_no');
                            
                            echo \kartik\select2\Select2::widget([
                                'name' => 'target_job_id',
                                'data' => $listData,
                                'options' => ['placeholder' => 'ค้นหาใบงาน...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'dropdownParent' => '#mergeJobModal'
                                ],
                            ]);
                        ?>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i> <b>คำเตือน:</b> <br>
                        1. รายละเอียดสินค้าจากใบงานที่เลือก จะถูกเพิ่มต่อท้ายใบงานนี้ (เฉพาะสินค้าที่ไม่ซ้ำ)<br>
                        2. ใบงานที่เลือกจะถูกเปลี่ยนสถานะเป็น <b>"ยกเลิก"</b> ทันที
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('ยืนยันการรวมข้อมูล? ใบงานที่ถูกเลือกจะถูกยกเลิก');">ยืนยันการรวม</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
