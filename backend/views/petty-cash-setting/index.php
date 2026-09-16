<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $maxAmount backend\models\SystemSetting */
/* @var $minAmount backend\models\SystemSetting */

$this->title = 'ตั้งค่าวงเงินสดย่อย';
$this->params['breadcrumbs'][] = ['label' => 'การจัดการเงินทดแทนสดย่อย', 'url' => ['/petty-cash-advance/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="petty-cash-setting-index">

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cog"></i> <?= Html::encode($this->title) ?></h3>
        </div>
        
        <?php $form = ActiveForm::begin(); ?>
        <div class="card-body">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_amount"><?= Html::encode($minAmount->description) ?></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="min_amount" name="min_amount" value="<?= Html::encode($minAmount->setting_value) ?>" required>
                            <div class="input-group-append">
                                <span class="input-group-text">บาท</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">ถ้ายอดคงเหลือน้อยกว่าหรือเท่ากับค่านี้ ระบบจะแจ้งเตือนให้เบิกชดเชย</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_amount"><?= Html::encode($maxAmount->description) ?></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="max_amount" name="max_amount" value="<?= Html::encode($maxAmount->setting_value) ?>" required>
                            <div class="input-group-append">
                                <span class="input-group-text">บาท</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">วงเงินสูงสุดที่สามารถเบิกได้ทั้งหมด</small>
                    </div>
                </div>
            </div>

        </div>
        
        <div class="card-footer text-right">
            <?= Html::a('ยกเลิก', ['/petty-cash-advance/index'], ['class' => 'btn btn-default']) ?>
            <?= Html::submitButton('<i class="fas fa-save"></i> บันทึกการตั้งค่า', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

</div>
