<?php
$this->title = 'สำรองข้อมูล (Database & Files Backup)';
$this->params['breadcrumbs'][] = '/ ' . $this->title;

$backupDir = dirname(__DIR__, 2) . '/web/uploads/backup';
$files = glob($backupDir . '/*.sql');
if ($files) {
    // Sort descending by modified time
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
}

$defaultSourceDir = realpath(Yii::getAlias('@backend/web/uploads'));
if (!$defaultSourceDir) {
    $defaultSourceDir = realpath(dirname(__DIR__, 2) . '/web/uploads');
}

$isLocal = \backend\controllers\DbbackupController::isLocalEnvironment();
$liveServerUrl = \backend\controllers\DbbackupController::getLiveServerUrl();
$syncSecretKey = \backend\controllers\DbbackupController::getSyncSecretKey();
?>
<br>
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="icon fa fa-check mr-1"></i> <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="icon fa fa-ban mr-1"></i> <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Control Panel Card -->
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h3 class="card-title text-primary font-weight-bold mb-0">
            <i class="fas fa-database mr-1"></i> แผงควบคุมการสำรองข้อมูล (Database Backup)
        </h3>
        <div>
            <!-- ปุ่ม Backup MySQL แบบ Manual พร้อม Progress Bar & Modal -->
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btn-backup-mysql" onclick="startBackupMysql()">
                <i class="fas fa-database mr-1"></i> สำรองฐานข้อมูล MySQL เดี๋ยวนี้ (Manual Backup)
            </button>
        </div>
    </div>
</div>

<?php if ($isLocal): ?>
<!-- Live Server Sync Card (แสดงเฉพาะเครื่อง Localhost เพื่อความปลอดภัย 100%) -->
<div class="card card-outline card-warning shadow-sm mb-4 border-warning">
    <div class="card-header bg-warning py-2 text-dark d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold mb-0">
            <i class="fas fa-cloud-download-alt mr-1"></i> ดึงข้อมูลสำรองจาก Live Server ลงเครื่องนี้ (Pull & Sync from Live Server)
        </h3>
        <span class="badge badge-dark">โหมด Local PC / Disaster Recovery</span>
    </div>
    <div class="card-body bg-light">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <div class="form-row">
                    <div class="col-md-7 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-dark small mb-1">
                            <i class="fas fa-globe text-primary mr-1"></i> Live Server URL (โดเมนของ Server จริง):
                        </label>
                        <input type="text" id="sync-server-url" class="form-control form-control-sm" value="<?= htmlspecialchars($liveServerUrl) ?>" placeholder="https://your-domain.com">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label font-weight-bold text-dark small mb-1">
                            <i class="fas fa-key text-warning mr-1"></i> Sync Secret Key (รหัสกุญแจความปลอดภัย):
                        </label>
                        <input type="password" id="sync-secret-key" class="form-control form-control-sm" value="<?= htmlspecialchars($syncSecretKey) ?>" placeholder="กุญแจความปลอดภัย">
                    </div>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-shield-alt text-success"></i> ระบบจะสตรีมฐานข้อมูลจาก Server มา Restore เข้า MySQL Local และดาวน์โหลดเฉพาะไฟล์รูปภาพ/ไฟล์แนบใหม่ที่ยังไม่มีในเครื่องลงโฟลเดอร์ <code>backend/web/uploads</code> อัตโนมัติ
                </small>
            </div>
            <div class="col-lg-5 text-lg-right">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2" style="gap: 8px;">
                    <!-- ดึงเฉพาะฐานข้อมูล DB -->
                    <button type="button" class="btn btn-outline-dark btn-sm font-weight-bold" id="btn-pull-db" onclick="startPullRemoteDb()">
                        <i class="fas fa-database text-warning mr-1"></i> ดึงเฉพาะ DB จาก Server
                    </button>
                    <!-- ดึงเฉพาะไฟล์ Uploads -->
                    <button type="button" class="btn btn-outline-info btn-sm font-weight-bold" id="btn-pull-files" onclick="startPullRemoteFiles()">
                        <i class="fas fa-images text-info mr-1"></i> ดึงเฉพาะไฟล์แนบใหม่
                    </button>
                    <!-- ดึงทั้งหมด DB + ไฟล์แนบ -->
                    <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm" id="btn-pull-all" onclick="startPullRemoteAll()">
                        <i class="fas fa-sync-alt mr-1"></i> ดึงทั้งหมด (DB + ไฟล์แนบ)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SQL Backup List Card -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-database text-primary mr-1"></i> รายการไฟล์สำรองฐานข้อมูล (.sql)
        </h3>
        <div class="card-tools">
            <a href="<?= \yii\helpers\Url::to(['dbrestore/restorepage'], true) ?>" class="btn btn-outline-info btn-sm">
                <i class="fas fa-upload"></i> อัปโหลดไฟล์เพื่อ Restore
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="table-backup-files">
                <thead class="thead-light">
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>ชื่อไฟล์ (File Name)</th>
                    <th>วันที่และเวลาสำรอง (Date/Time)</th>
                    <th>ขนาดไฟล์ (Size)</th>
                    <th class="text-center" style="width: 320px;">การจัดการ (Action)</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($files)): ?>
                    <?php $i = 1; foreach ($files as $file): ?>
                        <?php 
                            $basename = basename($file);
                            $sizeMB = number_format(filesize($file) / (1024 * 1024), 2);
                            $fileDate = date('d/m/Y H:i:s', filemtime($file));
                        ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>
                                <i class="far fa-file-code text-primary mr-1"></i>
                                <strong><?= htmlspecialchars($basename) ?></strong>
                            </td>
                            <td><?= $fileDate ?></td>
                            <td><span class="badge badge-secondary" style="font-size: 0.9em;"><?= $sizeMB ?> MB</span></td>
                            <td class="text-center">
                                <a href="<?= \yii\helpers\Url::to(['dbbackup/downloadbak', 'id' => $basename], true) ?>" class="btn btn-success btn-xs">
                                    <i class="fa fa-download"></i> ดาวน์โหลด
                                </a>
                                <a href="<?= \yii\helpers\Url::to(['dbbackup/restorefile', 'id' => $basename], true) ?>" 
                                   class="btn btn-warning btn-xs" 
                                   onclick="return confirm('คำเตือน: การ Restore จะทับข้อมูลปัจจุบันด้วยข้อมูลในไฟล์นี้ทันที!\nคุณแน่ใจหรือไม่ที่จะกู้คืนจากไฟล์: <?= htmlspecialchars($basename) ?>?');">
                                    <i class="fa fa-undo"></i> Restore ไฟล์นี้
                                </a>
                                <a href="<?= \yii\helpers\Url::to(['dbbackup/deletebak', 'id' => $basename], true) ?>" 
                                   class="btn btn-danger btn-xs" 
                                   onclick="return confirm('ยืนยันการลบไฟล์ <?= htmlspecialchars($basename) ?>?');">
                                    <i class="fa fa-trash"></i> ลบ
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            <i class="fas fa-folder-open fa-2x mb-2 text-secondary"></i><br>
                            ยังไม่มีไฟล์สำรองข้อมูลในระบบ กรุณากดปุ่ม <strong>"1. สำรองเฉพาะ MySQL"</strong> หรือ <strong>"3. สำรองทั้งหมด"</strong>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Fullscreen Blocking Modal for Progress -->
<div class="modal fade" id="modal-backup-progress" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 600px;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="progress-modal-title">
                    <i class="fas fa-sync-alt fa-spin mr-2"></i> กำลังดำเนินการสำรองข้อมูล...
                </h5>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div id="progress-status-icon" class="mb-2">
                        <i class="fas fa-cog fa-spin fa-3x text-primary"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1" id="progress-step-text">เริ่มต้นการทำงาน...</h5>
                    <div class="text-muted small" id="progress-detail-text">กรุณารอสักครู่ ห้ามปิดหน้าต่างหรือเปลี่ยนหน้าจนกว่าจะเสร็จสิ้น</div>
                </div>

                <!-- Main Progress Bar -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between small font-weight-bold mb-1">
                        <span id="progress-bar-label">ความคืบหน้า</span>
                        <span id="progress-bar-percent" class="text-primary">0%</span>
                    </div>
                    <div class="progress" style="height: 22px; border-radius: 11px; background-color: #e9ecef;">
                        <div id="progress-bar-fill" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" style="width: 0%; font-weight: bold; font-size: 12px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                             0%
                        </div>
                    </div>
                </div>

                <!-- Stats Badges -->
                <div class="d-flex justify-content-around text-center mt-3 pt-2 border-top small text-secondary">
                    <div>
                        <div class="text-dark font-weight-bold" id="stat-total-files">0</div>
                        <div>ไฟล์ทั้งหมด</div>
                    </div>
                    <div>
                        <div class="text-success font-weight-bold" id="stat-copied-files">0</div>
                        <div>คัดลอกแล้ว</div>
                    </div>
                    <div>
                        <div class="text-info font-weight-bold" id="stat-skipped-files">0</div>
                        <div>ไฟล์เดิม (ข้าม)</div>
                    </div>
                    <div>
                        <div class="text-primary font-weight-bold" id="stat-speed-mb">0 MB</div>
                        <div>ขนาดข้อมูล</div>
                    </div>
                </div>

                <!-- Realtime Log Box -->
                <div class="mt-3">
                    <div class="small font-weight-bold text-muted mb-1"><i class="fas fa-terminal mr-1"></i> บันทึกการทำงานล่าสุด (Live Log):</div>
                    <div id="progress-log-box" class="p-2 border rounded bg-light font-monospace small" 
                         style="height: 110px; overflow-y: auto; font-size: 11px; line-height: 1.4; color: #333;">
                        <div>[ระบบ] พร้อมเริ่มต้นการทำงาน...</div>
                    </div>
                </div>

                <!-- Completed Alert (Hidden initially) -->
                <div id="progress-complete-box" class="alert alert-success mt-3 mb-0 d-none text-center">
                    <i class="fas fa-check-circle fa-2x mb-1 d-block"></i>
                    <strong>ดำเนินการเสร็จสมบูรณ์เรียบร้อย 100%!</strong>
                    <div id="progress-summary-text" class="small mt-1"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2" id="progress-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm px-4 d-none" id="btn-close-progress" data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="fas fa-check mr-1"></i> ตกลง / ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Folder Picker Modal (Tree / Directory Browser) -->
<div class="modal fade" id="modal-folder-picker" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-folder-open text-warning mr-2"></i> เลือกโฟลเดอร์ปลายทาง (Browse Folder)
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex align-items-center mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="picker-btn-up" title="ขึ้นไป 1 ระดับ">
                        <i class="fas fa-arrow-up"></i> ขึ้นบน
                    </button>
                    <div class="flex-grow-1">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-folder text-warning"></i></span>
                            </div>
                            <input type="text" id="picker-current-path" class="form-control" placeholder="พิมพ์ Path หรือไดรฟ์ เช่น C:\ หรือ E:\ แล้วกด ไป">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="picker-btn-go" title="เปิดโฟลเดอร์นี้">
                                    <i class="fas fa-arrow-right"></i> ไป
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drive buttons (Windows) -->
                <div id="picker-drives-container" class="mb-2">
                    <div class="d-flex flex-wrap gap-1 align-items-center" id="picker-drives-list" style="gap: 5px;"></div>
                </div>

                <!-- Folder list container -->
                <div class="border rounded bg-white" style="height: 320px; overflow-y: auto;" id="picker-folder-list-wrap">
                    <ul class="list-group list-group-flush" id="picker-folder-list">
                        <!-- Loaded dynamically -->
                    </ul>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                <div>
                    <span class="text-muted small">
                        <i class="fas fa-mouse-pointer mr-1"></i> ดับเบิ้ลคลิกโฟลเดอร์เพื่อเปิดเข้าไปข้างใน
                    </span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm px-3 mr-1" data-dismiss="modal" data-bs-dismiss="modal">
                        ยกเลิก
                    </button>
                    <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold" id="picker-btn-select-current">
                        <i class="fas fa-check mr-1"></i> เลือกโฟลเดอร์นี้
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Overlay Backdrop บล็อกการคลิกส่วนอื่น */
#modal-backup-progress {
    background: rgba(0, 0, 0, 0.75) !important;
    z-index: 99999 !important;
}
#modal-backup-progress.show,
#modal-backup-progress[style*="display: block"] {
    display: block !important;
    opacity: 1 !important;
}
#modal-folder-picker {
    z-index: 99998 !important;
}
.modal-backdrop {
    z-index: 99990 !important;
}
.font-monospace {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
.picker-folder-item {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s ease;
}
.picker-folder-item:hover {
    background-color: #f1f8ff;
}
.picker-folder-item.selected {
    background-color: #007bff !important;
    color: #fff !important;
}
.picker-folder-item.selected i {
    color: #fff !important;
}
</style>

<?php
$urlBackupDb = \yii\helpers\Url::to(['dbbackup/backup-db-ajax'], true);
$urlGetFiles = \yii\helpers\Url::to(['dbbackup/get-files-list'], true);
$urlCopyBatch = \yii\helpers\Url::to(['dbbackup/copy-batch'], true);
$urlBrowseFolder = \yii\helpers\Url::to(['dbbackup/browse-folder'], true);
$urlPullRemoteDb = \yii\helpers\Url::to(['dbbackup/pull-remote-db'], true);
$urlPullRemoteFilesList = \yii\helpers\Url::to(['dbbackup/pull-remote-files-list'], true);
$urlPullRemoteFileBatch = \yii\helpers\Url::to(['dbbackup/pull-remote-file-batch'], true);
?>

<script>
// Expose global placeholder/proxy functions so HTML onclick attributes never throw ReferenceError
window.startBackupMysql = function() {
    if (typeof window._doBackupMysql === 'function') {
        window._doBackupMysql();
    } else {
        alert('ระบบกำลังโหลด กรุณารอสักครู่แล้วลองอีกครั้ง');
    }
};

window.startSyncUploads = function() {
    if (typeof window._doSyncUploads === 'function') {
        window._doSyncUploads();
    } else {
        alert('ระบบกำลังโหลด กรุณารอสักครู่แล้วลองอีกครั้ง');
    }
};

window.startBackupAll = function() {
    if (typeof window._doBackupAll === 'function') {
        window._doBackupAll();
    } else {
        alert('ระบบกำลังโหลด กรุณารอสักครู่แล้วลองอีกครั้ง');
    }
};

window.startPullRemoteDb = function() {
    if (typeof window._doPullRemoteDb === 'function') {
        window._doPullRemoteDb();
    } else {
        alert('ระบบกำลังโหลด กรุณารอสักครู่แล้วลองอีกครั้ง');
    }
};

window.startPullRemoteFiles = function() {
    if (typeof window._doPullRemoteFiles === 'function') {
        window._doPullRemoteFiles();
    } else {
        alert('ระบบกำลังโหลด กรุณารอสักครู่แล้วลองอีกครั้ง');
    }
};

window.startPullRemoteAll = function() {
    if (typeof window._doPullRemoteAll === 'function') {
        window._doPullRemoteAll();
    } else {
        alert('ระบบกำลังโหลด กรุณารอสักครู่แล้วลองอีกครั้ง');
    }
};

(function initBackupSystem(){
    if (typeof jQuery === 'undefined') {
        // ถ้า jQuery ยังไม่พร้อม (โหลดอยู่ด้านล่างของหน้า) ให้รอจนกว่าจะพร้อม
        document.addEventListener('DOMContentLoaded', initBackupSystem);
        return;
    }
    var $ = jQuery;
    var isWorking = false;
    var defaultDest = <?= json_encode($defaultSourceDir) ?>;
    var urlBackupDb = <?= json_encode($urlBackupDb) ?>;
    var urlGetFiles = <?= json_encode($urlGetFiles) ?>;
    var urlCopyBatch = <?= json_encode($urlCopyBatch) ?>;
    var urlBrowseFolder = <?= json_encode($urlBrowseFolder) ?>;
    var urlPullRemoteDb = <?= json_encode($urlPullRemoteDb) ?>;
    var urlPullRemoteFilesList = <?= json_encode($urlPullRemoteFilesList) ?>;
    var urlPullRemoteFileBatch = <?= json_encode($urlPullRemoteFileBatch) ?>;

    function addLog(msg, color) {
        var box = $('#progress-log-box');
        var colorStyle = color ? 'style="color:' + color + ';"' : '';
        box.append('<div ' + colorStyle + '>[' + new Date().toLocaleTimeString() + '] ' + msg + '</div>');
        box.scrollTop(box[0].scrollHeight);
    }

    function resetModal() {
        $('#progress-status-icon').html('<i class="fas fa-cog fa-spin fa-3x text-primary"></i>');
        $('#progress-step-text').text('กำลังจัดเตรียมข้อมูล...');
        $('#progress-detail-text').text('กรุณารอสักครู่ ห้ามปิดหน้าต่างหรือคลิกส่วนอื่นจนกว่าจะเสร็จสิ้น');
        $('#progress-bar-percent').text('0%');
        $('#progress-bar-fill').css('width', '0%').text('0%').removeClass('bg-danger bg-warning').addClass('bg-success');
        $('#stat-total-files').text('0');
        $('#stat-copied-files').text('0');
        $('#stat-skipped-files').text('0');
        $('#stat-speed-mb').text('0 MB');
        $('#progress-log-box').html('');
        $('#progress-complete-box').addClass('d-none');
        $('#btn-close-progress').addClass('d-none');
    }

    function openModal(selector, options) {
        var modalTarget = $(selector);
        if (!modalTarget.length) return;

        // บังคับแสดง Modal บนหน้าจอทันทีเพื่อความรวดเร็วและแน่นอน 100%
        modalTarget.addClass('show').css({
            'display': 'block',
            'opacity': '1',
            'z-index': '99999'
        }).removeAttr('aria-hidden');
        $('body').addClass('modal-open');

        // เรียก Bootstrap modal API เพื่อ trigger event backdrop
        try {
            if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
                modalTarget.modal(options || {backdrop: 'static', keyboard: false, show: true});
                return;
            }
        } catch(e) {
            console.warn('Bootstrap 4 jQuery modal call failed:', e);
        }

        try {
            if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
                var modalObj = window.bootstrap.Modal.getInstance(modalTarget[0]) || new window.bootstrap.Modal(modalTarget[0], options || {});
                modalObj.show();
                return;
            }
        } catch(e) {
            console.warn('Bootstrap 5 modal call failed:', e);
        }

        // Fallback backdrop หาก Bootstrap modal backdrop ไม่ขึ้น
        if (!$('.modal-backdrop').length) {
            $('body').append('<div class="modal-backdrop fade show modal-fallback-backdrop" style="z-index: 99990;"></div>');
        }
    }

    function closeModal(selector) {
        var modalTarget = $(selector);
        if (!modalTarget.length) return;

        try {
            if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
                modalTarget.modal('hide');
            }
        } catch(e) {}

        try {
            if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
                var modalObj = window.bootstrap.Modal.getInstance(modalTarget[0]);
                if (modalObj) modalObj.hide();
            }
        } catch(e) {}

        // Fallback cleanup
        modalTarget.removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
        $('.modal-fallback-backdrop').remove();
        if (!$('.modal.show').length) {
            $('body').removeClass('modal-open').css('padding-right', '');
        }
    }

    function lockUI() {
        isWorking = true;
        // ปิดปุ่มควบคุมภายนอก แต่ไม่ปิดปุ่มใน modal
        $('#btn-backup-mysql, #btn-sync-uploads, #btn-backup-all, #btn-browse-dest, #btn-reset-dest, #btn-pull-db, #btn-pull-files, #btn-pull-all').prop('disabled', true);
        window.onbeforeunload = function() {
            return "กำลังสำรองข้อมูลอยู่ในเบื้องหลัง หากออกจากหน้านี้กระบวนการอาจไม่สมบูรณ์!";
        };
        openModal('#modal-backup-progress', {backdrop: 'static', keyboard: false});
    }

    function unlockUI() {
        isWorking = false;
        $('#btn-backup-mysql, #btn-sync-uploads, #btn-backup-all, #btn-browse-dest, #btn-reset-dest, #btn-pull-db, #btn-pull-files, #btn-pull-all').prop('disabled', false);
        window.onbeforeunload = null;
        $('#btn-close-progress').removeClass('d-none');
    }

    function setProgress(percent, label) {
        percent = Math.min(100, Math.max(0, Math.round(percent)));
        $('#progress-bar-percent').text(percent + '%');
        $('#progress-bar-fill').css('width', percent + '%').text(percent + '%');
        if (label) {
            $('#progress-step-text').text(label);
        }
    }

    // Reset default destination button
    $('#btn-reset-dest').on('click', function(){
        $('#dest-path-input').val(defaultDest);
    });

    // ==========================================
    // Browse Folder Picker Logic
    // ==========================================
    var pickerCurrentPath = '';
    var pickerParentPath = null;
    var selectedItemPath = '';

    function renderPickerDirectory(targetPath) {
        var list = $('#picker-folder-list');
        list.html('<li class="list-group-item text-center text-muted p-4"><i class="fas fa-spinner fa-spin mr-1"></i> กำลังโหลดโฟลเดอร์...</li>');

        $.ajax({
            url: urlBrowseFolder,
            type: 'POST',
            data: {
                targetPath: targetPath,
                mode: 'list'
            },
            dataType: 'json'
        }).done(function(res){
            if (res.status !== 'success') {
                list.html('<li class="list-group-item text-center text-danger p-3"><i class="fas fa-exclamation-triangle mr-1"></i> ไม่สามารถเข้าถึงโฟลเดอร์นี้ได้</li>');
                return;
            }

            pickerCurrentPath = res.currentPath;
            pickerParentPath = res.parentPath;
            selectedItemPath = '';
            $('#picker-current-path').val(pickerCurrentPath);

            // Up button state
            if (pickerParentPath) {
                $('#picker-btn-up').prop('disabled', false).attr('data-path', pickerParentPath);
            } else {
                $('#picker-btn-up').prop('disabled', true);
            }

            // Render Drives (if available)
            var drivesContainer = $('#picker-drives-container');
            var drivesList = $('#picker-drives-list');
            drivesList.empty();
            if (res.drives && res.drives.length > 0) {
                drivesContainer.removeClass('d-none');
                $.each(res.drives, function(idx, drv){
                    var btn = $('<button type="button" class="btn btn-outline-info btn-xs mr-1"></button>')
                        .html('<i class="fas fa-hdd mr-1"></i> ' + drv.name)
                        .attr('data-path', drv.path);
                    if (pickerCurrentPath.toLowerCase().indexOf(drv.path.toLowerCase().replace('/', '\\')) === 0 || 
                        pickerCurrentPath.toLowerCase().indexOf(drv.path.toLowerCase()) === 0) {
                        btn.removeClass('btn-outline-info').addClass('btn-info text-white font-weight-bold');
                    }
                    drivesList.append(btn);
                });
            } else {
                drivesContainer.addClass('d-none');
            }

            // Render Subfolders
            list.empty();
            if (!res.folders || res.folders.length === 0) {
                list.append('<li class="list-group-item text-center text-muted p-3"><i class="far fa-folder-open mr-1"></i> ไม่มีโฟลเดอร์ย่อยข้างในนี้</li>');
                return;
            }

            $.each(res.folders, function(idx, item){
                var li = $('<li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center picker-folder-item py-2 px-3"></li>')
                    .attr('data-path', item.path)
                    .html('<span><i class="fas fa-folder text-warning mr-2"></i> ' + $('<div>').text(item.name).html() + '</span>' +
                          '<button type="button" class="btn btn-outline-primary btn-xs btn-open-sub" title="เปิดโฟลเดอร์นี้"><i class="fas fa-chevron-right"></i></button>');
                list.append(li);
            });
        }).fail(function(xhr, status, error){
            var msg = 'เชื่อมต่อล้มเหลว (' + status + (error ? ': ' + error : '') + ')';
            if (xhr.status === 403) msg = 'ไม่มีสิทธิ์เข้าถึง (403 Forbidden)';
            if (xhr.status === 404) msg = 'ไม่พบ URL ปลายทาง (404 Not Found)';
            list.html('<li class="list-group-item text-center text-danger p-3"><i class="fas fa-times-circle mr-1"></i> ' + msg + '</li>');
        });
    }

    // เมื่อ Modal เลือกโฟลเดอร์ถูกเปิดขึ้นมา (ไม่ว่าจะคลิกปุ่ม หรือ trigger ผ่าน data-toggle)
    $('#modal-folder-picker').on('show.bs.modal', function(){
        var curVal = $.trim($('#dest-path-input').val()) || defaultDest;
        renderPickerDirectory(curVal);
    });

    // คลิกปุ่ม Browse... เปิดหน้าต่างเลือกโฟลเดอร์ทันที (Failsafe)
    $(document).on('click', '#btn-browse-dest', function(e){
        e.preventDefault();
        if (isWorking) return;

        openModal('#modal-folder-picker');
        var curVal = $.trim($('#dest-path-input').val()) || defaultDest;
        renderPickerDirectory(curVal);
    });

    // คลิกเลือก Drive
    $(document).on('click', '#picker-drives-list button', function(){
        var p = $(this).attr('data-path');
        if (p) renderPickerDirectory(p);
    });

    // ปุ่ม ไป หรือกด Enter ในช่อง Path ใน Modal
    $('#picker-btn-go').on('click', function(){
        var p = $.trim($('#picker-current-path').val());
        if (p) renderPickerDirectory(p);
    });
    $('#picker-current-path').on('keypress', function(e){
        if (e.which === 13) {
            e.preventDefault();
            var p = $.trim($(this).val());
            if (p) renderPickerDirectory(p);
        }
    });

    // ปุ่มขึ้นไป 1 ระดับ
    $('#picker-btn-up').on('click', function(){
        var p = $(this).attr('data-path');
        if (p) renderPickerDirectory(p);
    });

    // คลิกเลือกโฟลเดอร์ (Highlight)
    $(document).on('click', '.picker-folder-item', function(e){
        if ($(e.target).closest('.btn-open-sub').length) return;
        $('.picker-folder-item').removeClass('selected');
        $(this).addClass('selected');
        selectedItemPath = $(this).attr('data-path');
        $('#picker-current-path').val(selectedItemPath);
    });

    // ดับเบิ้ลคลิกเพื่อเปิดโฟลเดอร์ หรือกดปุ่มลูกศร
    $(document).on('dblclick', '.picker-folder-item', function(){
        var p = $(this).attr('data-path');
        if (p) renderPickerDirectory(p);
    });
    $(document).on('click', '.btn-open-sub', function(e){
        e.stopPropagation();
        var p = $(this).closest('.picker-folder-item').attr('data-path');
        if (p) renderPickerDirectory(p);
    });

    // ยืนยันเลือกโฟลเดอร์นี้
    $('#picker-btn-select-current').on('click', function(){
        var chosen = $('#picker-current-path').val() || selectedItemPath || pickerCurrentPath;
        if (chosen) {
            $('#dest-path-input').val(chosen);
        }
        closeModal('#modal-folder-picker');
    });

    // ฟังก์ชันย่อยสำหรับ Copy Files
    function executeCopyUploads(destPath, onCompleteCallback) {
        addLog('กำลังสแกนรายการไฟล์และรูปภาพใน uploads...', '#17a2b8');
        $('#progress-step-text').text('กำลังตรวจสอบรายการไฟล์...');
        setProgress(5, 'สแกนรายการไฟล์...');

        $.ajax({
            url: urlGetFiles,
            type: 'POST',
            data: { destPath: destPath },
            dataType: 'json'
        }).done(function(res){
            if (res.status !== 'success') {
                $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                $('#progress-step-text').text('เกิดข้อผิดพลาดในการสแกนไฟล์');
                addLog('Error: ' + res.message, 'red');
                alert(res.message);
                if (onCompleteCallback) onCompleteCallback(false);
                return;
            }

            var files = res.files || [];
            var totalFiles = files.length;
            var totalSizeMB = res.totalSizeMB || '0';
            $('#stat-total-files').text(totalFiles.toLocaleString());
            $('#stat-speed-mb').text(totalSizeMB + ' MB');
            addLog('พบไฟล์ทั้งหมด: ' + totalFiles + ' ไฟล์ (รวม ' + totalSizeMB + ' MB)', '#007bff');
            addLog('โฟลเดอร์ปลายทาง: ' + res.destDir);

            if (totalFiles === 0) {
                setProgress(100, 'ไม่มีไฟล์ในโฟลเดอร์ uploads');
                addLog('ไม่มีไฟล์ที่ต้องคัดลอก', 'green');
                if (onCompleteCallback) onCompleteCallback(true);
                return;
            }

            // แบ่งไฟล์เป็นชุดๆ ละ 50 ไฟล์เพื่อส่ง Ajax copy
            var batchSize = 50;
            var batches = [];
            for (var i = 0; i < totalFiles; i += batchSize) {
                batches.push(files.slice(i, i + batchSize));
            }

            var currentBatchIdx = 0;
            var totalCopied = 0;
            var totalSkipped = 0;

            function processNextBatch() {
                if (currentBatchIdx >= batches.length) {
                    setProgress(100, 'คัดลอกไฟล์รูปภาพและเอกสารแนบเสร็จสมบูรณ์!');
                    addLog('เสร็จสิ้นการคัดลอก: คัดลอกใหม่ ' + totalCopied + ' ไฟล์, ไฟล์เดิม ' + totalSkipped + ' ไฟล์', 'green');
                    if (onCompleteCallback) onCompleteCallback(true);
                    return;
                }

                var batch = batches[currentBatchIdx];
                var currentBatchNum = currentBatchIdx + 1;
                addLog('กำลังคัดลอกชุดที่ ' + currentBatchNum + '/' + batches.length + ' (' + batch.length + ' ไฟล์)...', '#6c757d');

                $.ajax({
                    url: urlCopyBatch,
                    type: 'POST',
                    data: {
                        destPath: destPath,
                        batch: batch
                    },
                    dataType: 'json'
                }).done(function(batchRes){
                    if (batchRes.status === 'success') {
                        var justCopied = batchRes.copiedCount || 0;
                        var justSkipped = batchRes.skippedCount || 0;
                        totalCopied += justCopied;
                        totalSkipped += justSkipped;
                        $('#stat-copied-files').text(totalCopied.toLocaleString());
                        $('#stat-skipped-files').text(totalSkipped.toLocaleString());

                        var processedFiles = (currentBatchIdx + 1) * batchSize;
                        if (processedFiles > totalFiles) processedFiles = totalFiles;

                        var pct = Math.round((processedFiles / totalFiles) * 100);
                        setProgress(pct, 'กำลังคัดลอกไฟล์ (' + processedFiles.toLocaleString() + '/' + totalFiles.toLocaleString() + ' ไฟล์ - ' + pct + '%)');

                        if (justCopied > 0) {
                            addLog('ชุดที่ ' + currentBatchNum + ': คัดลอกไฟล์ใหม่ ' + justCopied + ' ไฟล์' + (justSkipped > 0 ? ' (ข้ามไฟล์เดิม ' + justSkipped + ')' : ''), '#28a745');
                        } else if (justSkipped > 0) {
                            addLog('ชุดที่ ' + currentBatchNum + ': ปลายทางมีไฟล์เดิมอยู่แล้ว ' + justSkipped + ' ไฟล์ (ข้ามอัตโนมัติ ไม่เขียนทับ)', '#17a2b8');
                        }

                        if (batchRes.errors && batchRes.errors.length > 0) {
                            for (var e = 0; e < batchRes.errors.length; e++) {
                                addLog('คำเตือน: ' + batchRes.errors[e], '#e0a800');
                            }
                        }

                        currentBatchIdx++;
                        processNextBatch();
                    } else {
                        addLog('ข้อผิดพลาด: ' + batchRes.message, 'red');
                        alert(batchRes.message);
                        if (onCompleteCallback) onCompleteCallback(false);
                    }
                }).fail(function(xhr, status, error){
                    addLog('เชื่อมต่อขัดข้องขณะคัดลอกชุดที่ ' + currentBatchNum + ': ' + error, 'red');
                    alert('เกิดข้อผิดพลาด: ' + error);
                    if (onCompleteCallback) onCompleteCallback(false);
                });
            }

            processNextBatch();

        }).fail(function(xhr, status, error){
            addLog('Ajax error scanning files: ' + error, 'red');
            alert('ไม่สามารถสแกนรายการไฟล์ได้: ' + error);
            if (onCompleteCallback) onCompleteCallback(false);
        });
    }

    // 1. สำรองเฉพาะ MySQL
    window._doBackupMysql = window.startBackupMysql = function() {
        if (isWorking) return;
        if (!confirm('ยืนยันการสำรองฐานข้อมูล MySQL เดี๋ยวนี้?')) return;

        resetModal();
        lockUI();
        $('#progress-modal-title').html('<i class="fas fa-database mr-2"></i> กำลังสำรองฐานข้อมูล MySQL...');
        addLog('เริ่มต้นการสำรองข้อมูล MySQL Dump...', '#007bff');
        setProgress(20, 'กำลัง Dump ฐานข้อมูล MySQL...');

        $.ajax({
            url: urlBackupDb,
            type: 'POST',
            dataType: 'json',
            timeout: 600000 // 10 minutes timeout
        }).done(function(res){
            if (res.status === 'success') {
                setProgress(100, 'สำรองฐานข้อมูล MySQL สำเร็จ 100%');
                $('#progress-status-icon').html('<i class="fas fa-check-circle fa-3x text-success"></i>');
                addLog('ไฟล์ SQL: ' + res.fileName + ' (' + res.fileSize + ')', 'green');
                addLog('ใช้เวลา: ' + res.duration, 'green');
                $('#progress-complete-box').removeClass('d-none');
                $('#progress-summary-text').html(res.message);
                unlockUI();
            } else {
                $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                $('#progress-step-text').text('การสำรองฐานข้อมูลล้มเหลว');
                $('#progress-bar-fill').addClass('bg-danger');
                addLog('ข้อผิดพลาด: ' + res.message, 'red');
                alert('เกิดข้อผิดพลาด: ' + res.message);
                unlockUI();
            }
        }).fail(function(xhr, status, error){
            $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
            $('#progress-step-text').text('การเชื่อมต่อขัดข้อง');
            $('#progress-bar-fill').addClass('bg-danger');
            addLog('AJAX Error: ' + error, 'red');
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error);
            unlockUI();
        });
    };

    // 2. ปุ่ม Copy ไฟล์ Uploads อย่างเดียว
    window._doSyncUploads = window.startSyncUploads = function() {
        console.log('startSyncUploads triggered');
        if (isWorking) {
            console.warn('System is already working, click ignored');
            return;
        }
        var destPath = $.trim($('#dest-path-input').val()) || defaultDest;
        if (!destPath) {
            alert('กรุณาระบุหรือเลือกโฟลเดอร์ปลายทาง');
            $('#dest-path-input').focus();
            return;
        }

        if (!confirm('ยืนยันการคัดลอกไฟล์รูปภาพและไฟล์แนบจากระบบไปยัง:\n' + destPath + ' ?')) {
            return;
        }

        resetModal();
        lockUI();
        $('#progress-modal-title').html('<i class="fas fa-images mr-2"></i> กำลัง Copy ไฟล์รูปภาพและไฟล์แนบ...');

        try {
            executeCopyUploads(destPath, function(success){
                if (success) {
                    $('#progress-status-icon').html('<i class="fas fa-check-circle fa-3x text-success"></i>');
                    $('#progress-complete-box').removeClass('d-none');
                    $('#progress-summary-text').html('คัดลอกไฟล์แนบและรูปภาพทั้งหมดลงเครื่องปลายทางเรียบร้อยแล้ว');
                } else {
                    $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                    $('#progress-bar-fill').addClass('bg-danger');
                }
                unlockUI();
            });
        } catch(err) {
            console.error('executeCopyUploads error:', err);
            alert('เกิดข้อผิดพลาดในการเริ่มต้นคัดลอก: ' + err.message);
            unlockUI();
        }
    };

    // 3. ปุ่ม สำรองทั้งหมด (DB + Copy Uploads)
    window._doBackupAll = window.startBackupAll = function() {
        if (isWorking) return;
        var destPath = $.trim($('#dest-path-input').val()) || defaultDest;
        if (!destPath) {
            alert('กรุณาระบุโฟลเดอร์ปลายทาง');
            $('#dest-path-input').focus();
            return;
        }

        if (!confirm('ยืนยันการสำรองฐานข้อมูล MySQL พร้อมคัดลอกไฟล์แนบทั้งหมด?')) return;

        resetModal();
        lockUI();
        $('#progress-modal-title').html('<i class="fas fa-layer-group mr-2"></i> กำลังสำรองฐานข้อมูล และ คัดลอกไฟล์แนบ...');

        // ขั้นที่ 1: Backup MySQL ก่อน
        addLog('[ขั้นตอนที่ 1/2] เริ่มต้นสำรองฐานข้อมูล MySQL...', '#007bff');
        setProgress(10, '[1/2] กำลังสำรองฐานข้อมูล MySQL...');

        $.ajax({
            url: urlBackupDb,
            type: 'POST',
            dataType: 'json',
            timeout: 600000
        }).done(function(res){
            if (res.status === 'success') {
                addLog('สำรอง MySQL สำเร็จ: ' + res.fileName + ' (' + res.fileSize + ')', 'green');
                setProgress(30, '[1/2] สำรอง MySQL สำเร็จ -> กำลังเริ่มขั้นตอนที่ 2');

                // ขั้นที่ 2: เริ่ม Copy Uploads
                addLog('[ขั้นตอนที่ 2/2] เริ่มต้นคัดลอกไฟล์รูปภาพและไฟล์แนบ...', '#17a2b8');
                executeCopyUploads(destPath, function(filesSuccess){
                    if (filesSuccess) {
                        $('#progress-status-icon').html('<i class="fas fa-check-circle fa-3x text-success"></i>');
                        $('#progress-step-text').text('สำรองฐานข้อมูลและคัดลอกไฟล์เสร็จสิ้น 100%');
                        $('#progress-complete-box').removeClass('d-none');
                        $('#progress-summary-text').html('สำรองฐานข้อมูล ' + res.fileName + ' และคัดลอกไฟล์แนบเรียบร้อยแล้ว');
                    } else {
                        $('#progress-status-icon').html('<i class="fas fa-exclamation-triangle fa-3x text-warning"></i>');
                        $('#progress-step-text').text('สำรอง MySQL สำเร็จ แต่คัดลอกไฟล์ไม่สมบูรณ์');
                        $('#progress-bar-fill').addClass('bg-warning');
                    }
                    unlockUI();
                });

            } else {
                $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                $('#progress-step-text').text('การสำรองฐานข้อมูลล้มเหลว');
                $('#progress-bar-fill').addClass('bg-danger');
                addLog('DB Backup Error: ' + res.message, 'red');
                alert('เกิดข้อผิดพลาดในการสำรอง DB: ' + res.message);
                unlockUI();
            }
        }).fail(function(xhr, status, error){
            $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
            $('#progress-step-text').text('การเชื่อมต่อขัดข้อง');
            $('#progress-bar-fill').addClass('bg-danger');
            addLog('AJAX Error: ' + error, 'red');
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error);
            unlockUI();
        });
    };

    // ==========================================
    // REMOTE SYNC / PULL FROM LIVE SERVER LOGIC
    // ==========================================

    function executePullRemoteFiles(onCompleteCallback) {
        addLog('กำลังเชื่อมต่อ Live Server เพื่อตรวจสอบรายการไฟล์แนบ...', '#17a2b8');
        $('#progress-step-text').text('กำลังตรวจสอบรายการไฟล์จาก Server...');
        setProgress(5, 'ติดต่อ Live Server...');

        $.ajax({
            url: urlPullRemoteFilesList,
            type: 'POST',
            dataType: 'json',
            timeout: 180000
        }).done(function(res){
            if (res.status !== 'success') {
                $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                $('#progress-step-text').text('ไม่สามารถดึงรายการไฟล์จาก Server ได้');
                addLog('Error: ' + res.message, 'red');
                alert(res.message);
                if (onCompleteCallback) onCompleteCallback(false);
                return;
            }

            var serverTotal = res.serverTotalFiles || 0;
            var alreadyExist = res.alreadyExistCount || 0;
            var needSync = res.needSyncCount || 0;
            var needSyncSizeMB = res.needSyncSizeMB || '0';

            $('#stat-total-files').text(serverTotal.toLocaleString());
            $('#stat-skipped-files').text(alreadyExist.toLocaleString());
            $('#stat-speed-mb').text(needSyncSizeMB + ' MB');

            addLog('พบไฟล์บน Server: ' + serverTotal.toLocaleString() + ' ไฟล์', '#007bff');
            addLog('ไฟล์ที่ในเครื่อง Local มีอยู่แล้ว: ' + alreadyExist.toLocaleString() + ' ไฟล์ (ข้ามการดาวน์โหลด)', '#28a745');
            addLog('ไฟล์ใหม่/ต้องดาวน์โหลด: ' + needSync.toLocaleString() + ' ไฟล์ (' + needSyncSizeMB + ' MB)', '#fd7e14');

            if (needSync === 0) {
                setProgress(100, 'ไฟล์แนบและรูปภาพในเครื่อง Local เป็นปัจจุบันเท่ากับ Server 100% แล้ว');
                addLog('ไม่มีไฟล์ใหม่ที่ต้องดาวน์โหลด ข้อมูลไฟล์แนบสมบูรณ์แล้ว', 'green');
                if (onCompleteCallback) onCompleteCallback(true);
                return;
            }

            var files = res.files || [];
            var batchSize = 25; // ดาวน์โหลดทีละ 25 ไฟล์เพื่อความเสถียรของ network
            var batches = [];
            for (var i = 0; i < files.length; i += batchSize) {
                batches.push(files.slice(i, i + batchSize));
            }

            var currentBatchIdx = 0;
            var totalDownloaded = 0;

            function processNextRemoteBatch() {
                if (currentBatchIdx >= batches.length) {
                    setProgress(100, 'ดาวน์โหลดไฟล์แนบและรูปภาพจาก Server ครบถ้วนแล้ว!');
                    addLog('เสร็จสิ้นการดาวน์โหลด: ดาวน์โหลดไฟล์ใหม่ทั้งหมด ' + totalDownloaded + ' ไฟล์', 'green');
                    if (onCompleteCallback) onCompleteCallback(true);
                    return;
                }

                var batch = batches[currentBatchIdx];
                var currentBatchNum = currentBatchIdx + 1;
                addLog('กำลังดาวน์โหลดชุดที่ ' + currentBatchNum + '/' + batches.length + ' (' + batch.length + ' ไฟล์)...', '#6c757d');

                $.ajax({
                    url: urlPullRemoteFileBatch,
                    type: 'POST',
                    data: { batch: batch },
                    dataType: 'json',
                    timeout: 180000
                }).done(function(batchRes){
                    if (batchRes.status === 'success') {
                        var justDownloaded = batchRes.downloadedCount || 0;
                        totalDownloaded += justDownloaded;
                        $('#stat-copied-files').text(totalDownloaded.toLocaleString());

                        var processedFiles = (currentBatchIdx + 1) * batchSize;
                        if (processedFiles > files.length) processedFiles = files.length;

                        var pct = Math.round((processedFiles / files.length) * 100);
                        setProgress(pct, 'กำลังดาวน์โหลดไฟล์ (' + processedFiles.toLocaleString() + '/' + files.length.toLocaleString() + ' ไฟล์ใหม่ - ' + pct + '%)');

                        addLog('ชุดที่ ' + currentBatchNum + ': ดาวน์โหลดสำเร็จ ' + justDownloaded + ' ไฟล์', '#28a745');

                        if (batchRes.errors && batchRes.errors.length > 0) {
                            for (var e = 0; e < batchRes.errors.length; e++) {
                                addLog('คำเตือน: ' + batchRes.errors[e], '#e0a800');
                            }
                        }

                        currentBatchIdx++;
                        processNextRemoteBatch();
                    } else {
                        addLog('ข้อผิดพลาด: ' + batchRes.message, 'red');
                        alert(batchRes.message);
                        if (onCompleteCallback) onCompleteCallback(false);
                    }
                }).fail(function(xhr, status, error){
                    addLog('เชื่อมต่อขัดข้องขณะดาวน์โหลดชุดที่ ' + currentBatchNum + ': ' + error, 'red');
                    alert('เกิดข้อผิดพลาดในการดาวน์โหลดชุดที่ ' + currentBatchNum + ': ' + error);
                    if (onCompleteCallback) onCompleteCallback(false);
                });
            }

            processNextRemoteBatch();

        }).fail(function(xhr, status, error){
            addLog('การเชื่อมต่อกับ Live Server ขัดข้อง: ' + error, 'red');
            alert('ไม่สามารถเชื่อมต่อ Server ได้: ' + error);
            if (onCompleteCallback) onCompleteCallback(false);
        });
    }

    // ฟังก์ชัน 1: ดึงเฉพาะ Database
    window._doPullRemoteDb = window.startPullRemoteDb = function() {
        if (isWorking) return;
        var sUrl = $.trim($('#sync-server-url').val());
        if (!sUrl) {
            alert('กรุณาระบุ Live Server URL');
            $('#sync-server-url').focus();
            return;
        }

        if (!confirm('ยืนยันการดึงฐานข้อมูลล่าสุดจาก Live Server มาทับ (Restore) เข้า MySQL Local เครื่องนี้?\n(ข้อมูลใน Local จะถูกอัปเดตให้เท่ากับ Server ทันที)')) {
            return;
        }

        resetModal();
        lockUI();
        $('#progress-modal-title').html('<i class="fas fa-cloud-download-alt mr-2"></i> กำลังดึงฐานข้อมูลจาก Live Server...');
        addLog('กำลังสั่งให้ Live Server ทำการ Dump Database...', '#007bff');
        setProgress(15, 'Live Server กำลังส่งฐานข้อมูลมายัง Local...');

        $.ajax({
            url: urlPullRemoteDb,
            type: 'POST',
            dataType: 'json',
            timeout: 600000
        }).done(function(res){
            if (res.status === 'success') {
                setProgress(100, 'ดึงและ Restore ฐานข้อมูลสำเร็จ 100%');
                $('#progress-status-icon').html('<i class="fas fa-check-circle fa-3x text-success"></i>');
                addLog(res.message, 'green');
                $('#progress-complete-box').removeClass('d-none');
                $('#progress-summary-text').html(res.message);
                unlockUI();
            } else {
                $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                $('#progress-step-text').text('การดึงฐานข้อมูลล้มเหลว');
                $('#progress-bar-fill').addClass('bg-danger');
                addLog('Error: ' + res.message, 'red');
                alert(res.message);
                unlockUI();
            }
        }).fail(function(xhr, status, error){
            $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
            $('#progress-step-text').text('การเชื่อมต่อขัดข้อง');
            $('#progress-bar-fill').addClass('bg-danger');
            addLog('AJAX Error: ' + error, 'red');
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error);
            unlockUI();
        });
    };

    // ฟังก์ชัน 2: ดึงเฉพาะไฟล์ Uploads
    window._doPullRemoteFiles = window.startPullRemoteFiles = function() {
        if (isWorking) return;
        var sUrl = $.trim($('#sync-server-url').val());
        if (!sUrl) {
            alert('กรุณาระบุ Live Server URL');
            $('#sync-server-url').focus();
            return;
        }

        if (!confirm('ยืนยันการดึงไฟล์แนบและรูปภาพใหม่จาก Live Server ลงเครื่องนี้?')) {
            return;
        }

        resetModal();
        lockUI();
        $('#progress-modal-title').html('<i class="fas fa-cloud-download-alt mr-2"></i> กำลังดึงไฟล์แนบและรูปภาพจาก Live Server...');

        executePullRemoteFiles(function(success){
            if (success) {
                $('#progress-status-icon').html('<i class="fas fa-check-circle fa-3x text-success"></i>');
                $('#progress-complete-box').removeClass('d-none');
                $('#progress-summary-text').html('ดึงไฟล์แนบและรูปภาพทั้งหมดลงเครื่อง Local เรียบร้อยแล้ว');
            } else {
                $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                $('#progress-bar-fill').addClass('bg-danger');
            }
            unlockUI();
        });
    };

    // ฟังก์ชัน 3: ดึงทั้งหมด (DB + Files)
    window._doPullRemoteAll = window.startPullRemoteAll = function() {
        if (isWorking) return;
        var sUrl = $.trim($('#sync-server-url').val());
        if (!sUrl) {
            alert('กรุณาระบุ Live Server URL');
            $('#sync-server-url').focus();
            return;
        }

        if (!confirm('ยืนยันการดึงข้อมูลทั้งหมด (ฐานข้อมูล + ไฟล์แนบ) จาก Live Server?\nเครื่อง Local จะถูกอัปเดตทั้ง DB และไฟล์แนบให้เท่ากับ Server จริง 100%')) {
            return;
        }

        resetModal();
        lockUI();
        $('#progress-modal-title').html('<i class="fas fa-sync-alt fa-spin mr-2"></i> กำลังดึงข้อมูลทั้งหมดจาก Live Server...');

        addLog('[ขั้นตอนที่ 1/2] กำลังดึงฐานข้อมูลจาก Live Server...', '#007bff');
        setProgress(10, '[1/2] ดึงและ Restore ฐานข้อมูล...');

        $.ajax({
            url: urlPullRemoteDb,
            type: 'POST',
            dataType: 'json',
            timeout: 600000
        }).done(function(res){
            if (res.status === 'success') {
                addLog('ฐานข้อมูล: ' + res.message, 'green');
                setProgress(35, '[1/2] สำรองฐานข้อมูลสำเร็จ -> เริ่มดึงไฟล์แนบ');

                addLog('[ขั้นตอนที่ 2/2] เริ่มต้นดาวน์โหลดไฟล์แนบและรูปภาพใหม่...', '#17a2b8');
                executePullRemoteFiles(function(filesSuccess){
                    if (filesSuccess) {
                        $('#progress-status-icon').html('<i class="fas fa-check-circle fa-3x text-success"></i>');
                        $('#progress-step-text').text('อัปเดตข้อมูลทั้งระบบจาก Live Server เสร็จสิ้น 100%');
                        $('#progress-complete-box').removeClass('d-none');
                        $('#progress-summary-text').html('ดึงฐานข้อมูลและไฟล์แนบทั้งหมดจาก Live Server มายัง Local สมบูรณ์ครบถ้วน 100%');
                    } else {
                        $('#progress-status-icon').html('<i class="fas fa-exclamation-triangle fa-3x text-warning"></i>');
                        $('#progress-step-text').text('ฐานข้อมูลสำเร็จ แต่การดาวน์โหลดไฟล์แนบไม่สมบูรณ์');
                        $('#progress-bar-fill').addClass('bg-warning');
                    }
                    unlockUI();
                });
            } else {
                $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
                $('#progress-step-text').text('การดึงฐานข้อมูลล้มเหลว');
                $('#progress-bar-fill').addClass('bg-danger');
                addLog('DB Error: ' + res.message, 'red');
                alert(res.message);
                unlockUI();
            }
        }).fail(function(xhr, status, error){
            $('#progress-status-icon').html('<i class="fas fa-times-circle fa-3x text-danger"></i>');
            $('#progress-step-text').text('การเชื่อมต่อขัดข้อง');
            $('#progress-bar-fill').addClass('bg-danger');
            addLog('AJAX Error: ' + error, 'red');
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error);
            unlockUI();
        });
    };

    // Binding ผ่าน jQuery Events ด้วยเช่นกัน
    $(document).on('click', '#btn-backup-mysql', function(e){
        e.preventDefault();
        window.startBackupMysql();
    });
    $(document).on('click', '#btn-sync-uploads', function(e){
        e.preventDefault();
        window.startSyncUploads();
    });
    $(document).on('click', '#btn-backup-all', function(e){
        e.preventDefault();
        window.startBackupAll();
    });
    $(document).on('click', '#btn-pull-db', function(e){
        e.preventDefault();
        window.startPullRemoteDb();
    });
    $(document).on('click', '#btn-pull-files', function(e){
        e.preventDefault();
        window.startPullRemoteFiles();
    });
    $(document).on('click', '#btn-pull-all', function(e){
        e.preventDefault();
        window.startPullRemoteAll();
    });

    // เมื่อปิด Modal ให้โหลดหน้านี้ใหม่เพื่อให้รายการไฟล์ SQL อัปเดตล่าสุด
    $('#btn-close-progress').on('click', function(){
        window.location.reload();
    });

})();
</script>
