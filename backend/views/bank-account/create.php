<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\BankAccount $model */

$this->title = 'สร้างบัญชีธนาคาร';
$this->params['breadcrumbs'][] = ['label' => 'บัญชีธนาคาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bank-account-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
