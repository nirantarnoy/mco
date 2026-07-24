<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Session;

$current_company_id = \Yii::$app->session->get('company_id');
$current_company_name = \Yii::$app->session->get('company_name') ?: 'เลือกบริษัท';

$companies = \backend\models\Company::find()->where(['status' => 1])->all();
if (empty($companies)) {
    $companies = \backend\models\Company::find()->all();
}

$user_info = null;
if (!Yii::$app->user->isGuest) {
    $user_info = \backend\models\User::find()->where(['id' => Yii::$app->user->id])->one();
}
$is_admin = ($user_info && $user_info->user_group_id == 1);
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
        </li>
        <li class="nav-item d-none d-sm-inline-block">
        </li>
        <li class="nav-item dropdown">
        </li>
    </ul>

    <!-- SEARCH FORM -->
    <form class="form-inline ml-3">
    </form>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto align-items-center">
        <!-- Company Selector Dropdown -->
        <li class="nav-item dropdown mr-3">
            <a class="nav-link dropdown-toggle btn btn-outline-info btn-sm text-dark px-3 py-1" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" style="border-radius: 20px; font-size: 13px; background-color: #eef2f5;">
                <i class="fas fa-building text-primary mr-1"></i>
                <span class="font-weight-bold"><?= Html::encode($current_company_name) ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow-lg border-0" style="min-width: 230px; border-radius: 10px;">
                <div class="dropdown-header font-weight-bold text-uppercase text-muted" style="font-size: 11px;">
                    <i class="fas fa-exchange-alt mr-1"></i> สลับบริษัทที่ใช้งาน
                </div>
                <div class="dropdown-divider mb-1"></div>

                <?php if ($is_admin): ?>
                    <a href="<?= Url::to(['/site/change-company', 'id' => 100]) ?>" 
                       class="dropdown-item py-2 <?= $current_company_id == 100 ? 'active bg-primary font-weight-bold' : '' ?>">
                        <i class="fas fa-globe mr-2"></i> ทุกบริษัท (ผู้ดูแลระบบ)
                        <?php if ($current_company_id == 100): ?>
                            <i class="fas fa-check float-right mt-1"></i>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-divider my-1"></div>
                <?php endif; ?>

                <?php foreach ($companies as $com): ?>
                    <a href="<?= Url::to(['/site/change-company', 'id' => $com->id]) ?>" 
                       class="dropdown-item py-2 <?= $current_company_id == $com->id ? 'active bg-primary font-weight-bold' : '' ?>">
                        <i class="fas fa-building mr-2 text-secondary"></i> <?= Html::encode($com->name) ?>
                        <?php if ($current_company_id == $com->id): ?>
                            <i class="fas fa-check float-right mt-1"></i>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </li>

        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <span><i class="fa fa-user-circle"></i>  <?= \backend\models\User::findName(\Yii::$app->user->id)?></span>
                <?php //echo $_SESSION['user_group_id']?>
                <!--                    <span class="badge badge-danger navbar-badge">3</span>-->
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <a href="index.php?r=site/changepassword" class="dropdown-item">
                    <!-- Message Start -->
                    <div class="media">
                        <img src="uploads/images/change_password.png" alt="User Avatar"
                             class="img-size-50 mr-3 img-circle">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                เปลี่ยนรหัสผ่าน
                            </h3>
                            <p class="text-sm">Change password</p>
                        </div>
                    </div>
                    <!-- Message End -->
                </a>
                <div class="dropdown-divider"></div>
                <a href="index.php?r=site/logout" class="dropdown-item">
                    <!-- Message Start -->
                    <div class="media">
                        <img src="uploads/images/logout.png" alt="User Avatar"
                             class="img-size-50 mr-3 img-circle">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                ออกจากระบบ
                            </h3>
                            <p class="text-sm">Logout</p>
                        </div>
                    </div>
                    <!-- Message End -->
                </a>
            </div>
        </li>
    </ul>
</nav>
