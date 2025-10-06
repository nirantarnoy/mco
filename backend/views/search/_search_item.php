<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

/* @var $model backend\models\Job */
?>

<div class="card search-result-item">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title mb-2">
                    <a href="<?= Url::to(['job/timeline', 'id' => $model->id]) ?>" class="text-primary font-weight-bold">
                        <i class="fas fa-briefcase"></i> <?= Html::encode($model->job_no) ?>
                    </a>
                    <?php if ($model->status): ?>
                        <span class="badge badge-<?= $model->status == 'completed' ? 'success' : ($model->status == 'in_progress' ? 'warning' : 'secondary') ?>">
                            <?= Html::encode($model->status) ?>
                        </span>
                    <?php endif; ?>
                </h5>

                <?php if ($model->quotation_id): ?>
                    <p class="text-muted mb-1">
                        <i class="fas fa-file-invoice"></i>
                        <strong>เลขที่ใบเสนอราคา:</strong> <?= Html::encode(\backend\models\Quotation::findNo($model->quotation_id)) ?>
                    </p>
                <?php endif; ?>

                <?php if ($model->job_date): ?>
                    <p class="text-muted mb-1">
                        <i class="far fa-calendar"></i>
                        <strong>วันที่ใบงาน:</strong> <?= date('m/d/Y',strtotime($model->job_date)) ?>
                    </p>
                <?php endif; ?>

                <?php if ($model->job_amount): ?>
                    <p class="text-muted mb-1">
<!--                        <i class="fas fa-dollar-sign"></i>-->
                        <strong>มูลค่างาน:</strong> <?= Yii::$app->formatter->asDecimal($model->job_amount, 2) ?>
                    </p>
                <?php endif; ?>

                <?php if ($model->summary_note): ?>
                    <p class="mb-2">
                        <i class="fas fa-sticky-note"></i>
                        <?= Html::encode(StringHelper::truncate($model->summary_note, 150)) ?>
                    </p>
                <?php endif; ?>

                <!-- Products in this job -->
                <?php if ($model->jobLines): ?>
                    <div class="mt-2">
                        <small class="text-muted"><i class="fas fa-box"></i> <strong>Products:</strong></small>
                        <div class="mt-1">
                            <?php
                            $displayCount = 0;
                            foreach ($model->jobLines as $jobLine):
                                if ($displayCount >= 3) break;
                                $displayCount++;
                                ?>
                                <span class="badge badge-light border mr-1 mb-1">
                                <?php if ($jobLine->product): ?>
                                    <?= Html::encode($jobLine->product->code) ?> -
                                    <?= Html::encode(StringHelper::truncate($jobLine->product->name, 30)) ?>
                                    <small class="text-muted">(Qty: <?= $jobLine->qty ?>)</small>
                                <?php else: ?>
                                    สินค้า: <?= $jobLine->product_id ?>
                                <?php endif; ?>
                            </span>
                            <?php endforeach; ?>
                            <?php if (count($model->jobLines) > 3): ?>
                                <span class="badge badge-secondary">+<?= count($model->jobLines) - 3 ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4 text-right">
                <div class="mb-2">
                    <?php if ($model->start_date && $model->end_date): ?>
                        <small class="text-muted d-block">
                            <i class="far fa-clock"></i> Duration
                        </small>
                        <span class="d-block">
                            <?= Yii::$app->formatter->asDate($model->start_date, 'php:d M Y') ?>
                        </span>
                        <small class="text-muted">to</small>
                        <span class="d-block">
                            <?= Yii::$app->formatter->asDate($model->end_date, 'php:d M Y') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <a href="<?= Url::to(['job/timeline', 'id' => $model->id]) ?>" class="btn btn-primary btn-sm mt-2">
                    <i class="fas fa-eye"></i> ดู Timeline
                </a>
            </div>
        </div>
    </div>
    <div class="card-footer text-muted">
        <small>
            <i class="far fa-clock"></i>
            สร้างเมื่อ: <?= date('m/d/Y',$model->created_at) ?>
            <?php if ($model->updated_at): ?>
                | แก้ไขเมื่อ: <?= date('m/d/Y',$model->updated_at) ?>
            <?php endif; ?>
        </small>
    </div>
</div>