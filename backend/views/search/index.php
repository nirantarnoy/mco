<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = 'ค้นหา';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="search-index">
        <div class="container-fluid">

            <!-- Search Form -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = \yii\bootstrap4\ActiveForm::begin([
                                'method' => 'get',
                                'action' => ['search/index'],
                            ]); ?>

                            <div class="input-group input-group-lg">
                                <input type="text"
                                       class="form-control"
                                       name="q"
                                       value="<?= Html::encode($searchQuery) ?>"
                                       placeholder="ค้นหา... (คั่นด้วย , สำหรับหลายคำ เช่น JOB001, เสื้อ, รอดำเนินการ)"
                                       autofocus>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> ค้นหา
                                    </button>
                                </div>
                            </div>

                            <?php if (!empty($searchQuery)): ?>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    กำลังค้นหา: <strong><?= Html::encode($searchQuery) ?></strong>
                                </small>
                            <?php endif; ?>

                            <?php \yii\bootstrap4\ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Results -->
            <?php if ($dataProvider !== null): ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php if ($dataProvider->getTotalCount() > 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle"></i>
                                พบ <strong><?= $dataProvider->getTotalCount() ?></strong> รายการ
                            </div>

                            <?= ListView::widget([
                                'dataProvider' => $dataProvider,
                                'itemView' => '_search_item',
                                'viewParams' => ['searchQuery' => $searchQuery],
                                'layout' => "{items}\n<div class='d-flex justify-content-center'>{pager}</div>",
                                'itemOptions' => ['class' => 'mb-3'],
                            ]); ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                ไม่พบผลลัพธ์สำหรับ "<strong><?= Html::encode($searchQuery) ?></strong>"
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif (empty($searchQuery)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">กรุณากรอกคำค้นหา</h4>
                                <p class="text-muted">ระบบจะค้นหาจาก Job No, Product, Status และข้อมูลอื่นๆ</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php
$this->registerCss("
.search-result-item {
    transition: all 0.3s ease;
}
.search-result-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
");
?>