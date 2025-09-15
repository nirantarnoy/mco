<?php
// ไฟล์: views/job/documents.php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use backend\models\Job;

/* @var $this yii\web\View */
/* @var $model Job */
/* @var $activityType string */
/* @var $activityId integer */
/* @var $activityName string */
/* @var $documents array */

$this->title = 'เอกสารแนบ: ' . $activityName;
$this->params['breadcrumbs'][] = ['label' => 'รายงานใบงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Timeline ใบงาน: ' . $model->job_no, 'url' => ['timeline', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="document-view">

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-0">
                                <i class="fas fa-paperclip"></i>
                                <?= Html::encode($this->title) ?>
                            </h4>
                            <small>ใบงาน: <?= Html::encode($model->job_no) ?> | กิจกรรม: <?= Html::encode($activityName) ?></small>
                        </div>
                        <div class="col-md-4 text-right">
                            <?= Html::a('<i class="fas fa-arrow-left"></i> กลับ Timeline', ['timeline', 'id' => $model->id], [
                                'class' => 'btn btn-light btn-sm'
                            ]) ?>
                            <?= Html::a('<i class="fas fa-print"></i> พิมพ์', '#', [
                                'class' => 'btn btn-warning btn-sm',
                                'onclick' => 'window.print(); return false;'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-folder-open"></i>
                        รายการเอกสารแนบ
                        <span class="badge badge-primary ml-2"><?= count($documents) ?> ไฟล์</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="documentsTable">
                                <thead class="thead-dark">
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th width="25%">ชื่อเอกสาร</th>
                                    <th width="15%">ประเภทไฟล์</th>
                                    <th width="10%" class="text-center">ขนาด</th>
                                    <th width="15%" class="text-center">วันที่อัพโหลด</th>
                                    <th width="15%">ผู้อัพโหลด</th>
                                    <th width="10%" class="text-center">สถานะ</th>
                                    <th width="15%" class="text-center">การดำเนินการ</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($documents as $index => $doc): ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <i class="fas <?= getFileIcon($doc['doc']) ?>"></i>
                                            <strong><?= Html::encode($doc['doc']) ?></strong>
                                            <?php if (!empty($doc['description'])): ?>
                                                <br><small class="text-muted"><?= Html::encode($doc['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $extension = strtoupper(pathinfo($doc['doc'], PATHINFO_EXTENSION));
                                            $badgeColor = getExtensionBadgeColor($extension);
                                            ?>
                                            <span class="badge badge-<?= $badgeColor ?>"><?= $extension ?: 'UNKNOWN' ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?= formatFileSize($doc['file_size'] ?? 0) ?>
                                        </td>
                                        <td class="text-center">
                                            <?= isset($doc['created_at']) ? date('d/m/Y H:i', strtotime($doc['created_at'])) : '-' ?>
                                        </td>
                                        <td>
                                            <?= Html::encode(\backend\models\User::findEmployeeNameByUserId($doc['created_by']) ?? '-') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $statusColor = ($doc['status'] ?? 1) == 1 ? 'success' : 'secondary';
                                            $statusText = ($doc['status'] ?? 1) == 1 ? 'ใช้งาน' : 'ไม่ใช้งาน';
                                            ?>
                                            <span class="badge badge-<?= $statusColor ?>"><?= $statusText ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <?= Html::a('<i class="fas fa-eye"></i>',
                                                    ['view-document', 'type' => $activityType, 'id' => $activityId, 'docId' => $doc['id']], [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'title' => 'ดูเอกสาร',
                                                        'target' => '_blank'
                                                    ]) ?>

                                                <?= Html::a('<i class="fas fa-download"></i>',
                                                    ['download-document', 'type' => $activityType, 'id' => $activityId, 'docId' => $doc['id']], [
                                                        'class' => 'btn btn-sm btn-outline-success',
                                                        'title' => 'ดาวน์โหลด'
                                                    ]) ?>

                                                <?= Html::a('<i class="fas fa-print"></i>',
                                                    ['print-document', 'type' => $activityType, 'id' => $activityId, 'docId' => $doc['id']], [
                                                        'class' => 'btn btn-sm btn-outline-info',
                                                        'title' => 'พิมพ์',
                                                        'target' => '_blank'
                                                    ]) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">จำนวนไฟล์ทั้งหมด</h6>
                                                    <h4 class="text-primary"><?= count($documents) ?> ไฟล์</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">ขนาดรวม</h6>
                                                    <h4 class="text-info"><?= formatFileSize(array_sum(array_column($documents, 'file_size'))) ?></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">ไฟล์ใช้งาน</h6>
                                                    <h4 class="text-success"><?= count(array_filter($documents, function($doc) { return ($doc['status'] ?? 1) == 1; })) ?> ไฟล์</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">อัพเดทล่าสุด</h6>
                                                    <h6 class="text-warning">
                                                        <?php
                                                        $latestDate = max(array_column($documents, 'created_at'));
                                                        echo $latestDate ? date('d/m/Y H:i', strtotime($latestDate)) : '-';
                                                        ?>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <h5>ไม่มีเอกสารแนบ</h5>
                            <p>ยังไม่มีเอกสารที่แนบไว้สำหรับกิจกรรมนี้</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper Functions
function getFileIcon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'fa-file-pdf text-danger',
        'doc' => 'fa-file-word text-primary',
        'docx' => 'fa-file-word text-primary',
        'xls' => 'fa-file-excel text-success',
        'xlsx' => 'fa-file-excel text-success',
        'ppt' => 'fa-file-powerpoint text-warning',
        'pptx' => 'fa-file-powerpoint text-warning',
        'jpg' => 'fa-file-image text-info',
        'jpeg' => 'fa-file-image text-info',
        'png' => 'fa-file-image text-info',
        'gif' => 'fa-file-image text-info',
        'txt' => 'fa-file-alt text-secondary',
        'zip' => 'fa-file-archive text-dark',
        'rar' => 'fa-file-archive text-dark',
    ];

    return $icons[$extension] ?? 'fa-file text-muted';
}

function getExtensionBadgeColor($extension) {
    $colors = [
        'PDF' => 'danger',
        'DOC' => 'primary',
        'DOCX' => 'primary',
        'XLS' => 'success',
        'XLSX' => 'success',
        'PPT' => 'warning',
        'PPTX' => 'warning',
        'JPG' => 'info',
        'JPEG' => 'info',
        'PNG' => 'info',
        'GIF' => 'info',
        'TXT' => 'secondary',
        'ZIP' => 'dark',
        'RAR' => 'dark',
    ];

    return $colors[$extension] ?? 'light';
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = floor(log($bytes, 1024));

    return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
}
?>

<style>
    .document-view .table th {
        background-color: #343a40;
        color: white;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
    }

    .document-view .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .badge {
        font-size: 0.75em;
    }

    @media print {
        .btn, .card-header .text-right {
            display: none !important;
        }

        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }

    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .btn-group .btn {
            margin-right: 0;
        }
    }
</style>

<script>
    $(document).ready(function() {
        // เพิ่ม DataTable ถ้ามี plugin
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#documentsTable').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Thai.json'
                }
            });
        }

        // เพิ่ม tooltip
        $('[title]').tooltip();

        // เพิ่ม confirmation สำหรับการดาวน์โหลด
        $('.btn-outline-success').click(function(e) {
            if (!confirm('คุณต้องการดาวน์โหลดไฟล์นี้หรือไม่?')) {
                e.preventDefault();
            }
        });
    });
</script>