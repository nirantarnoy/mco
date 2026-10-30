<?php
$this->title = 'กู้คืนข้อมูล (Database Restore)';
$this->params['breadcrumbs'][] = ['label' => 'สำรองข้อมูล', 'url' => ['dbbackup/backuplist']];
$this->params['breadcrumbs'][] = '/ ' . $this->title;
?>
<br>
<?php if (Yii::$app->session->hasFlash('msg')): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"></i> <?= Yii::$app->session->getFlash('msg') ?>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-ban"></i> <?= Yii::$app->session->getFlash('error') ?>
    </div>
<?php endif; ?>

<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-upload mr-1"></i> กู้คืนข้อมูลจากไฟล์ .sql (Restore Database)</h3>
        <div class="card-tools">
            <a href="<?= \yii\helpers\Url::to(['dbbackup/backuplist'], true) ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> กลับหน้ารายการ Backup
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h5><i class="icon fas fa-info-circle"></i> คำแนะนำและข้อควรระวัง:</h5>
            <ul>
                <li>ระบบทำงานผ่าน <code>MySQL CLI Engine</code> โดยตรง รองรับไฟล์ขนาดใหญ่ (เช่น <strong>60MB - 500MB+</strong>) ทำงานได้รวดเร็วและไม่ค้าง</li>
                <li>ก่อนทำการ Restore ขอแนะนำให้กด <strong>"สำรองข้อมูลเดี๋ยวนี้"</strong> ที่หน้า Backup เก็บไว้ก่อนทุกครั้ง</li>
                <li>ไฟล์ที่เลือกต้องเป็นไฟล์ <code>.sql</code> เท่านั้น</li>
                <li><strong>ขีดจำกัดการอัปโหลดไฟล์ผ่านเว็บของเซิร์ฟเวอร์ปัจจุบัน:</strong> 
                    <code>upload_max_filesize = <?= ini_get('upload_max_filesize') ?></code>, 
                    <code>post_max_size = <?= ini_get('post_max_size') ?></code>
                    <?php 
                        $uploadMax = ini_get('upload_max_filesize');
                        $postMax = ini_get('post_max_size');
                    ?>
                    <br><small class="text-muted">* หากไฟล์ .sql ของคุณมีขนาดใหญ่กว่าค่านี้ แนะนำให้อัปโหลดไฟล์ไปวางที่โฟลเดอร์ <code>backend/web/uploads/backup/</code> แล้วกด <strong>"Restore ไฟล์นี้"</strong> ที่หน้า Backup ได้ทันทีโดยไม่ต้องผ่านฟอร์มอัปโหลด หรือใช้คำสั่ง Console <code>php yii backup/restore</code></small>
                </li>
            </ul>
        </div>

        <form method="post" action="<?= \yii\helpers\Url::to(['dbrestore/restoredb'], true) ?>"
              enctype="multipart/form-data" id="form-restore">
            
            <div class="form-group">
                <label for="restore_file"><strong>เลือกไฟล์สำรองข้อมูล (.sql) ที่ต้องการกู้คืน:</strong></label>
                <div class="custom-file">
                    <input type="file" name="restore_file" id="restore_file" accept=".sql" class="custom-file-input" required onchange="updateFileName(this)">
                    <label class="custom-file-label" id="file-label" for="restore_file">เลือกไฟล์ .sql...</label>
                </div>
                <small class="form-text text-muted" id="file-size-info"></small>
            </div>

            <!-- Progress Bar Area -->
            <div id="restore-progress-container" style="display:none; margin-top: 20px; margin-bottom: 20px;">
                <label id="progress-status-text">กำลังดำเนินการ... กรุณารอสักครู่ (ห้ามปิดหน้านี้)</label>
                <div class="progress" style="height: 25px;">
                    <div id="restore-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 100%;">
                        กำลังอัปโหลดและนำเข้าข้อมูลสู่ฐานข้อมูล...
                    </div>
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-success btn-lg" id="btn-submit-restore">
                    <i class="fas fa-check-circle"></i> เริ่มต้นกู้คืนข้อมูล (Start Restore)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileName(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        document.getElementById('file-label').innerText = file.name;
        var sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        document.getElementById('file-size-info').innerHTML = '<span class="badge badge-info">ขนาดไฟล์: ' + sizeMB + ' MB</span>';
    }
}

document.getElementById('form-restore').addEventListener('submit', function(e) {
    var fileInput = document.getElementById('restore_file');
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('กรุณาเลือกไฟล์ .sql ก่อนครับ');
        e.preventDefault();
        return false;
    }

    var fileName = fileInput.files[0].name;
    var confirmed = confirm("คำเตือนสำคัญมาก!\n\nการ Restore จะนำเข้าข้อมูลจากไฟล์ " + fileName + " เข้าสู่ฐานข้อมูลจริงทันที\nคุณต้องการดำเนินการต่อหรือไม่?");
    if (!confirmed) {
        e.preventDefault();
        return false;
    }

    document.getElementById('btn-submit-restore').disabled = true;
    document.getElementById('restore-progress-container').style.display = 'block';
});
</script>
