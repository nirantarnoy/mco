<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;

$session = Yii::$app->session;
$this->title = "เปลี่ยนรหัสผ่านผู้ใช้งาน";

?>
<div class="panel panel-headline">
    <div class="panel-heading">
        <div class="x_title">
            <div class="clearfix"></div>
        </div>
        <div class="panel-body">
            <br/>
            <?php $form = ActiveForm::begin() ?>
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
                    <?php if ($session->getFlash('msg_err') != null): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $session->getFlash('msg_err') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if ($session->getFlash('msg_success') != null): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $session->getFlash('msg_success') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php
                    echo $form->field($model, 'oldpw')->passwordInput()->label();

                    ?>
                    <?php echo $form->field($model, 'newpw')->passwordInput()->label() ?>
                    <?php echo $form->field($model, 'confirmpw')->passwordInput()->label() ?>
                    <div class="form-group">
                        <input type="submit" value="ตกลง" class="btn btn-success">
                    </div>

                </div>
                <div class="col-lg-3"></div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
