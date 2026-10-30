<?php
$this->title = 'สำรองข้อมูล (Database Backup)';
$this->params['breadcrumbs'][] = '/ ' . $this->title;

$backupDir = dirname(__DIR__, 2) . '/web/uploads/backup';
$files = glob($backupDir . '/*.sql');
if ($files) {
    // Sort descending by modified time
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
}
?>
<br>
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"></i> <?= Yii::$app->session->getFlash('success') ?>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-ban"></i> <?= Yii::$app->session->getFlash('error') ?>
    </div>
<?php endif; ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-database mr-1"></i> รายการไฟล์สำรองฐานข้อมูล</h3>
        <div class="card-tools">
            <form action="<?= \yii\helpers\Url::to(['dbbackup/exrestore'], true) ?>" method="post" id="backup-form" style="display:inline-block;">
                <button type="submit" class="btn btn-primary btn-sm" id="btn-backup-start" onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> กำลังสำรองข้อมูล...'; this.form.submit();">
                    <i class="fas fa-plus-circle"></i> สำรองข้อมูลเดี๋ยวนี้ (Backup Now)
                </button>
            </form>
            <a href="<?= \yii\helpers\Url::to(['dbrestore/restorepage'], true) ?>" class="btn btn-info btn-sm ml-1">
                <i class="fas fa-upload"></i> อัปโหลดไฟล์เพื่อ Restore
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
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
                            ยังไม่มีไฟล์สำรองข้อมูลในระบบ กรุณากดปุ่ม <strong>"สำรองข้อมูลเดี๋ยวนี้"</strong>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
