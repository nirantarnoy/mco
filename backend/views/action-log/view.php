<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ActionLog */

$this->title = 'Action Log #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Action Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="action-log-view">

    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12">
            <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-default']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this log?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Main Information -->
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Log Details</h4>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'user_id',
                                'value' => $model->user_id ? $model->user_id : 'Guest',
                            ],
                            'username',
                            [
                                'attribute' => 'action',
                                'value' => $model->action,
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'font-weight: bold; color: #337ab7;'],
                            ],
                            'controller',
                            'action_method',
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => $model->getStatusLabel(),
                            ],
                            'ip_address',
                            [
                                'attribute' => 'url',
                                'format' => 'url',
                            ],
                            'method',
                            'model_class',
                            'model_id',
                            'created_at:datetime',
                            'updated_at:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Side Information -->
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="panel <?= $model->status === 'success' ? 'panel-success' : ($model->status === 'failed' ? 'panel-danger' : 'panel-warning') ?>">
                <div class="panel-heading">
                    <h4>Status Information</h4>
                </div>
                <div class="panel-body text-center">
                    <h3 style="margin: 0;">
                        <?= strtoupper($model->status) ?>
                    </h3>
                    <p style="margin: 10px 0 0 0;">
                        <?= date('M d, Y H:i:s', strtotime($model->created_at)) ?>
                    </p>
                </div>
            </div>

            <!-- User Information -->
            <?php if (!$model->user_id): ?>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h4>Guest User</h4>
                    </div>
                    <div class="panel-body">
                        <p><strong>No authenticated user</strong></p>
                        <p>IP: <?= Html::encode($model->ip_address) ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h4>User Information</h4>
                    </div>
                    <div class="panel-body">
                        <p><strong>ID:</strong> <?= $model->user_id ?></p>
                        <p><strong>Username:</strong> <?= Html::encode($model->username) ?></p>
                        <p><strong>IP:</strong> <?= Html::encode($model->ip_address) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Message Section -->
    <?php if (!empty($model->message)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Message</h4>
                    </div>
                    <div class="panel-body">
                        <pre><?= Html::encode($model->message) ?></pre>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Data Section -->
    <?php if (!empty($model->data)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Additional Data</h4>
                    </div>
                    <div class="panel-body">
                        <?php
                        $formattedData = $model->getFormattedData();
                        if (is_array($formattedData)): ?>
                            <pre><?= Html::encode(json_encode($formattedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        <?php else: ?>
                            <pre><?= Html::encode($model->data) ?></pre>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- User Agent Section -->
    <?php if (!empty($model->user_agent)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>User Agent</h4>
                    </div>
                    <div class="panel-body">
                        <small><?= Html::encode($model->user_agent) ?></small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Related Logs -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Related Logs (Same User, Last 24 Hours)</h4>
                </div>
                <div class="panel-body">
                    <?php
                    $relatedLogs = \app\models\ActionLog::find()
                        ->where(['user_id' => $model->user_id])
                        ->andWhere(['!=', 'id', $model->id])
                        ->andWhere(['>=', 'created_at', date('Y-m-d H:i:s', strtotime('-24 hours', strtotime($model->created_at)))])
                        ->orderBy(['created_at' => SORT_DESC])
                        ->limit(10)
                        ->all();

                    if ($relatedLogs): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>IP</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($relatedLogs as $log): ?>
                                    <tr>
                                        <td><?= date('H:i:s', strtotime($log->created_at)) ?></td>
                                        <td><?= Html::encode($log->action) ?></td>
                                        <td><?= $log->getStatusLabel() ?></td>
                                        <td><?= Html::encode($log->ip_address) ?></td>
                                        <td>
                                            <?= Html::a('View', ['view', 'id' => $log->id], ['class' => 'btn btn-xs btn-primary']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No related logs found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .panel {
        margin-bottom: 20px;
    }

    pre {
        background-color: #f5f5f5;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 10px;
        max-height: 300px;
        overflow-y: auto;
    }

    .table-condensed td {
        padding: 5px;
    }

    .label {
        font-size: 85%;
    }
</style>