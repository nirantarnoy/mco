<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'กำหนดสิทธิ์การใช้งาน (User Permission Matrix)';
$this->params['breadcrumbs'][] = $this->title;

// จัดกลุ่มโมดูลตาม group
$groupedModules = [];
foreach ($modules as $modKey => $conf) {
    $grp = isset($conf['group']) ? $conf['group'] : 'ทั่วไป';
    $groupedModules[$grp][$modKey] = $conf;
}

// กรองเฉพาะผู้ใช้งานที่มีสิทธิ์เข้าใช้งานบริษัทที่เลือกเท่านั้น
$activeCompanyUsers = [];
foreach ($users as $user) {
    $assignedComps = isset($userAssignedCompanies[$user->id]) ? $userAssignedCompanies[$user->id] : [];
    $hasCompAccess = in_array((int)$selectedCompanyId, $assignedComps);
    if ($hasCompAccess) {
        $activeCompanyUsers[] = $user;
    }
}

// สร้างไฟล์รายการตารางทั้งหมดในฐานข้อมูล
$dumpPath = Yii::getAlias('@backend/runtime/tables_dump.json');
if (!file_exists($dumpPath)) {
    try {
        $db = Yii::$app->db;
        $schema = $db->createCommand("SELECT DATABASE()")->queryScalar();
        $sql = "SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = :s ORDER BY TABLE_NAME ASC";
        $tbls = $db->createCommand($sql, [':s' => $schema])->queryAll();
        file_put_contents($dumpPath, json_encode($tbls, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    } catch (\Exception $e) {}
}
?>

<style>
    .matrix-table {
        background-color: #fff;
        border-collapse: separate;
        border-spacing: 0;
    }
    .matrix-table th, .matrix-table td {
        vertical-align: middle !important;
        text-align: center;
        border: 1px solid #dee2e6;
        padding: 6px 8px;
    }
    .matrix-table thead th {
        position: sticky;
        top: 0;
        background: #f4f6f9;
        z-index: 10;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .matrix-table thead th.freeze-col,
    .matrix-table tbody td.freeze-col {
        position: sticky;
        left: 0;
        background: #fff;
        z-index: 5;
        text-align: left;
        font-weight: 500;
        min-width: 260px;
    }
    .matrix-table thead th.freeze-col {
        z-index: 15;
        background: #e9ecef;
    }
    .group-header-row td {
        background-color: #e3f2fd !important;
        font-weight: bold;
        color: #0d47a1;
        text-align: left !important;
        padding: 8px 12px !important;
    }
    .group-header-row.approval-group td {
        background-color: #fff3cd !important;
        color: #856404 !important;
        border-top: 2px solid #ffeeba;
    }
    .perm-select {
        border-radius: 4px;
        font-weight: bold;
        padding: 3px 6px;
        font-size: 13px;
        cursor: pointer;
        width: 100%;
        text-align-last: center;
    }
    .perm-select[data-val="x"] {
        background-color: #f8f9fa;
        color: #dc3545;
        border: 1px solid #ced4da;
    }
    .perm-select[data-val="0"] {
        background-color: #e2f0d9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
    }
    .perm-select[data-val="1"] {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    .user-col-header {
        min-width: 110px;
    }
    .bulk-action-btn {
        font-size: 10px;
        padding: 1px 4px;
        margin: 1px;
    }
    .legend-box {
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 4px;
        vertical-align: middle;
        border-radius: 2px;
    }
</style>

<div class="user-permission-matrix">
    <!-- กล่องควบคุมด้านบน -->
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <!-- Dropdown เลือกบริษัทที่ต้องการกำหนดสิทธิ์ -->
                <div class="col-md-5">
                    <form method="get" action="<?= Url::to(['userpermission/index']) ?>" class="form-inline" id="company-form">
                        <input type="hidden" name="r" value="userpermission/index">
                        <label class="mr-2 font-weight-bold text-dark"><i class="fas fa-building text-primary"></i> เลือกบริษัท:</label>
                        <select name="company_id" class="form-control select2" onchange="document.getElementById('company-form').submit();" style="min-width: 260px;">
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?= $comp->id ?>" <?= $comp->id == $selectedCompanyId ? 'selected' : '' ?>>
                                    <?= Html::encode($comp->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- คำอธิบายความหมายของสัญลักษณ์ (Legend) -->
                <div class="col-md-4 text-center">
                    <span class="mr-3">
                        <span class="legend-box" style="background:#f8f9fa; border:1px solid #dc3545;"></span>
                        <strong class="text-danger">x</strong> = เข้าไม่ได้
                    </span>
                    <span class="mr-3">
                        <span class="legend-box" style="background:#e2f0d9; border:1px solid #2e7d32;"></span>
                        <strong class="text-success">0</strong> = ดูอย่างเดียว
                    </span>
                    <span>
                        <span class="legend-box" style="background:#d1ecf1; border:1px solid #0c5460;"></span>
                        <strong class="text-info">1</strong> = ทำรายการ
                    </span>
                </div>

                <!-- ปุ่มบันทึก และปุ่มเครื่องมือ -->
                <div class="col-md-3 text-right">
                    <button type="button" class="btn btn-success btn-sm px-2 shadow-sm" id="btn-save-all">
                        <i class="fas fa-save mr-1"></i> บันทึก
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm ml-1" data-toggle="modal" data-target="#userCompanyModal" title="กำหนดสิทธิ์การเข้าถึงบริษัทของ User">
                        <i class="fas fa-users-cog mr-1"></i> ผูกสิทธิ์บริษัท
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm ml-1" data-toggle="modal" data-target="#dataDictModal" title="ดู Data Dictionary ตารางในระบบ">
                        <i class="fas fa-book mr-1"></i> Data Dict
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ตาราง Matrix -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0" style="max-height: 75vh; overflow: auto;">
            <table class="table table-bordered table-hover matrix-table mb-0" id="matrix-table">
                <thead>
                    <tr>
                        <th class="freeze-col" style="min-width: 280px; text-align: left; background: #e9ecef;">
                            <strong>โมดูล / เมนูระบบ</strong>
                        </th>
                        <!-- สำหรับเลือกสิทธิ์ให้ทุกคน (Everyone) -->
                        <th style="min-width: 95px; background: #fff3cd;">
                            <div class="font-weight-bold text-dark">ทุกคน (All)</div>
                            <div class="mt-1">
                                <button type="button" class="btn btn-xs btn-outline-danger bulk-action-btn" onclick="setColumnValue('all', 'x')">x</button>
                                <button type="button" class="btn btn-xs btn-outline-success bulk-action-btn" onclick="setColumnValue('all', '0')">0</button>
                                <button type="button" class="btn btn-xs btn-outline-info bulk-action-btn" onclick="setColumnValue('all', '1')">1</button>
                            </div>
                        </th>
                        <!-- รายชื่อ User แต่ละคน (เฉพาะที่มีสิทธิ์ในบริษัทนี้) -->
                        <?php foreach ($activeCompanyUsers as $user): ?>
                            <th class="user-col-header" data-user-id="<?= $user->id ?>">
                                <div class="font-weight-bold text-primary">
                                    <?= Html::encode($user->username) ?>
                                </div>
                                <div class="small text-muted" style="font-size: 11px;">
                                    <?= \backend\models\User::findEmployeeNameByUserId($user->id) ?>
                                </div>
                                <!-- ปุ่ม Quick Set ทั้งคอลัมน์ -->
                                <div class="mt-1">
                                    <button type="button" class="btn btn-xs btn-outline-danger bulk-action-btn" onclick="setColumnValue(<?= $user->id ?>, 'x')">x</button>
                                    <button type="button" class="btn btn-xs btn-outline-success bulk-action-btn" onclick="setColumnValue(<?= $user->id ?>, '0')">0</button>
                                    <button type="button" class="btn btn-xs btn-outline-info bulk-action-btn" onclick="setColumnValue(<?= $user->id ?>, '1')">1</button>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedModules as $grpName => $grpMods): ?>
                        <?php $isApprovalGroup = (strpos($grpName, 'สิทธิ์การอนุมัติ') !== false); ?>
                        <tr class="group-header-row <?= $isApprovalGroup ? 'approval-group' : '' ?>">
                            <td colspan="<?= count($activeCompanyUsers) + 2 ?>" class="font-weight-bold">
                                <i class="<?= $isApprovalGroup ? 'fas fa-shield-alt text-warning' : 'fas fa-folder-open' ?> mr-1"></i> <?= Html::encode($grpName) ?>
                                <?php if ($isApprovalGroup): ?>
                                    <span class="badge badge-warning ml-2 font-weight-normal" style="font-size: 11px;">
                                        <i class="fas fa-info-circle"></i> สำหรับกำหนดบทบาทผู้อนุมัติ (เลือก 1 = มีสิทธิ์อนุมัติ/จัดการ Recall, x = ไม่มีสิทธิ์)
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php foreach ($grpMods as $modKey => $conf): ?>
                            <tr>
                                <td class="freeze-col" style="text-align: left;">
                                    <span class="font-weight-bold text-dark"><?= Html::encode($conf['name']) ?></span>
                                    <div class="small text-muted"><?= Html::encode($conf['description']) ?></div>
                                </td>

                                <!-- เซ็ตแถวนี้ให้ทุกคน -->
                                <td style="background: #fffdf5;">
                                    <select class="form-control form-control-sm perm-select row-all-select" onchange="setRowValue('<?= $modKey ?>', this.value)">
                                        <option value="">-</option>
                                        <option value="x">x</option>
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                    </select>
                                </td>

                                <!-- ค่าสิทธิ์ของแต่ละ User -->
                                <?php foreach ($activeCompanyUsers as $user): ?>
                                    <?php
                                        $val = isset($currentPermissions[$user->id][$modKey]) ? $currentPermissions[$user->id][$modKey] : 'x';
                                    ?>
                                    <td>
                                        <select class="form-control form-control-sm perm-select matrix-cell" 
                                                data-user-id="<?= $user->id ?>" 
                                                data-module-key="<?= $modKey ?>"
                                                data-val="<?= $val ?>"
                                                onchange="updateCellColor(this);">
                                            <option value="x" <?= $val === 'x' ? 'selected' : '' ?>>x</option>
                                            <option value="0" <?= $val === '0' ? 'selected' : '' ?>>0</option>
                                            <option value="1" <?= $val === '1' ? 'selected' : '' ?>>1</option>
                                        </select>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal กำหนดการเข้าถึงบริษัทของแต่ละ User -->
<div class="modal fade" id="userCompanyModal" tabindex="-1" role="dialog" aria-labelledby="userCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="userCompanyModalLabel"><i class="fas fa-building mr-1"></i> กำหนดสิทธิ์บริษัทให้กับผู้ใช้งาน (Multi-Company Assignment)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">เลือกบริษัทที่ต้องการให้ผู้ใช้งานแต่ละคนมีสิทธิ์เข้าถึง (หากไม่เลือก ผู้ใช้จะไม่สามารถสลับไปทำงานบริษัทนั้นได้)</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 250px;">ผู้ใช้งาน (User)</th>
                                <th>บริษัทที่ได้รับสิทธิ์</th>
                                <th style="width: 100px; text-align: center;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <?php $assigned = isset($userAssignedCompanies[$u->id]) ? $userAssignedCompanies[$u->id] : []; ?>
                                <tr>
                                    <td>
                                        <strong><?= Html::encode($u->username) ?></strong>
                                        <div class="text-muted small"><?= \backend\models\User::findEmployeeNameByUserId($u->id) ?></div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <?php foreach ($companies as $c): ?>
                                                <div class="col-md-6 mb-1">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input user-comp-check-<?= $u->id ?>" 
                                                               id="comp_<?= $u->id ?>_<?= $c->id ?>" 
                                                               value="<?= $c->id ?>"
                                                               <?= in_array($c->id, $assigned) ? 'checked' : '' ?>>
                                                        <label class="custom-control-label" for="comp_<?= $u->id ?>_<?= $c->id ?>">
                                                            <?= Html::encode($c->name) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="if(window.saveUserCompany){window.saveUserCompany(<?= $u->id ?>, this);}else{alert('ระบบกำลังโหลด กรุณารอสักครู่');}">
                                            บันทึก
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Data Dictionary -->
<div class="modal fade" id="dataDictModal" tabindex="-1" role="dialog" aria-labelledby="dataDictModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="dataDictModalLabel"><i class="fas fa-database mr-2 text-warning"></i> Data Dictionary: พจนานุกรมตารางฐานข้อมูลในระบบ (Database Tables)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="input-group" style="max-width: 380px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="filter-dict" class="form-control" placeholder="ค้นหาชื่อตาราง หรือคำอธิบาย...">
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" onclick="copyDictTable()">
                            <i class="fas fa-copy mr-1"></i> Copy ตาราง (ไปวางใน Excel / Word)
                        </button>
                        <button type="button" class="btn btn-success ml-1" onclick="exportDictToCSV()">
                            <i class="fas fa-file-excel mr-1"></i> ดาวน์โหลดเป็น Excel (CSV)
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                    <table class="table table-bordered table-striped table-hover" id="dict-table">
                        <thead class="thead-dark" style="position: sticky; top: 0; z-index: 5;">
                            <tr>
                                <th style="width: 70px; text-align: center;">ลำดับ</th>
                                <th style="width: 320px;">ชื่อตาราง (Table Name)</th>
                                <th>คำอธิบาย (Description / รายละเอียดการเก็บข้อมูล)</th>
                                <th style="width: 180px;">หมวดหมู่งาน</th>
                            </tr>
                        </thead>
                        <tbody id="dict-table-body">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> กำลังโหลดข้อมูลตาราง...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<?php
$saveUrl = Url::to(['userpermission/save']);
$assignUrl = Url::to(['userpermission/assign-companies']);
$dataDictUrl = Url::to(['userpermission/data-dictionary']);
$selectedCompIdJson = json_encode($selectedCompanyId);

$saveUrlJson = json_encode($saveUrl);
$assignUrlJson = json_encode($assignUrl);
$dataDictUrlJson = json_encode($dataDictUrl);

$js = <<<JS

function updateCellColor(selectEl) {
    var val = $(selectEl).val();
    $(selectEl).attr('data-val', val);
}

// Quick set ทั้งคอลัมน์ของ User
function setColumnValue(userId, value) {
    if (userId === 'all') {
        $('.matrix-cell:not([disabled])').each(function() {
            $(this).val(value);
            updateCellColor(this);
        });
    } else {
        $('.matrix-cell[data-user-id="' + userId + '"]:not([disabled])').each(function() {
            $(this).val(value);
            updateCellColor(this);
        });
    }
}

// Quick set ทั้งแถวของโมดูล
window.setRowValue = function(modKey, value) {
    if (!value) return;
    $('.matrix-cell[data-module-key="' + modKey + '"]:not([disabled])').each(function() {
        $(this).val(value);
        updateCellColor(this);
    });
};

window.setColumnValue = setColumnValue;

// บันทึกการผูกบริษัท
function saveUserCompany(userId, btnEl) {
    var selectedCompIds = [];
    $('.user-comp-check-' + userId + ':checked').each(function() {
        selectedCompIds.push($(this).val());
    });

    var btnTarget = btnEl ? $(btnEl) : $('.btn-save-user-company[data-user-id="' + userId + '"]');
    var originalHtml = btnTarget.html();
    btnTarget.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');

    $.ajax({
        url: {$assignUrlJson},
        type: 'POST',
        data: {
            user_id: userId,
            company_ids: selectedCompIds,
            _csrf: (typeof yii !== 'undefined' && yii.getCsrfToken) ? yii.getCsrfToken() : ''
        },
        dataType: 'json',
        success: function(res) {
            btnTarget.prop('disabled', false).html(originalHtml);
            if (res && res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: res.message || 'บันทึกข้อมูลเรียบร้อย',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    alert(res.message || 'บันทึกข้อมูลเรียบร้อย');
                    location.reload();
                }
            } else {
                var errMsg = (res && res.message) ? res.message : 'เกิดข้อผิดพลาดในการบันทึก';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: errMsg
                    });
                } else {
                    alert(errMsg);
                }
            }
        },
        error: function(xhr, status, error) {
            btnTarget.prop('disabled', false).html(originalHtml);
            var errText = 'การเชื่อมต่อผิดพลาด (' + (error || status) + ')';
            if (xhr.status === 403) errText = 'ไม่มีสิทธิ์เข้าถึง (403 Forbidden)';
            if (xhr.status === 404) errText = 'ไม่พบ URL บันทึก (404 Not Found)';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: errText
                });
            } else {
                alert(errText);
            }
        }
    });
}
window.saveUserCompany = saveUserCompany;

// Event Delegation สำหรับปุ่มบันทึกบริษัท
$(document).on('click', '.btn-save-user-company', function(e) {
    e.preventDefault();
    var uId = $(this).attr('data-user-id') || $(this).data('user-id');
    if (uId) {
        saveUserCompany(uId, this);
    }
});

// บันทึกค่า Matrix
$('#btn-save-all').on('click', function() {
    var btn = $(this);
    var matrixData = {};

    $('.matrix-cell:not([disabled])').each(function() {
        var uId = $(this).attr('data-user-id');
        var mKey = $(this).attr('data-module-key');
        var val = $(this).val();

        if (!matrixData[uId]) {
            matrixData[uId] = {};
        }
        matrixData[uId][mKey] = val;
    });

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> กำลังบันทึก...');

    $.ajax({
        url: {$saveUrlJson},
        type: 'POST',
        data: {
            company_id: {$selectedCompIdJson},
            matrix: matrixData
        },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> <strong>บันทึกการกำหนดสิทธิ์</strong>');
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: res.message
                });
            }
        },
        error: function(err) {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> <strong>บันทึกการกำหนดสิทธิ์</strong>');
            Swal.fire({
                icon: 'error',
                title: 'การเชื่อมต่อผิดพลาด',
                text: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง'
            });
        }
    });
});

// Data Dictionary Logic
var dataDictList = [];

// Mapping คำอธิบายตารางในโปรเจ็ค MCO
var tableDescriptions = {
    // 1. ทั่วไป, ผู้ใช้งาน และสิทธิ์
    'user': { desc: 'ข้อมูลบัญชีผู้ใช้งานระบบ (Username, Password, อีเมล, สถานะการใช้งาน)', group: 'ผู้ใช้งาน & สิทธิ์' },
    'usergroup': { desc: 'กลุ่มผู้ใช้งานระบบ', group: 'ผู้ใช้งาน & สิทธิ์' },
    'user_company': { desc: 'การผูกสิทธิ์ว่าผู้ใช้งาน (User) แต่ละคนสามารถเข้าใช้งานบริษัทใดได้บ้าง (Multi-Company)', group: 'ผู้ใช้งาน & สิทธิ์' },
    'user_module_permission': { desc: 'สิทธิ์การเข้าถึงและการทำงานในแต่ละโมดูลของ User แยกตามบริษัท (Matrix Permission: x=เข้าไม่ได้, 0=ดูอย่างเดียว, 1=ทำรายการ)', group: 'ผู้ใช้งาน & สิทธิ์' },
    'auth_assignment': { desc: 'การกำหนดบทบาท (Role) ของระบบ RBAC ให้กับผู้ใช้งาน', group: 'ผู้ใช้งาน & สิทธิ์' },
    'auth_item': { desc: 'รายการบทบาท (Role) และสิทธิ์ย่อย (Permission Route) ในระบบ RBAC', group: 'ผู้ใช้งาน & สิทธิ์' },
    'auth_item_child': { desc: 'ความสัมพันธ์ระหว่างบทบาทหลักและสิทธิ์ย่อยใน RBAC', group: 'ผู้ใช้งาน & สิทธิ์' },
    'auth_rule': { desc: 'กฎหรือเงื่อนไขพิเศษเพิ่มเติมของ RBAC', group: 'ผู้ใช้งาน & สิทธิ์' },
    'action_logs': { desc: 'บันทึกประวัติการใช้งานและกิจกรรมต่างๆ ของผู้ใช้ในระบบ (Audit Log / Activity Log)', group: 'ผู้ใช้งาน & สิทธิ์' },
    'company': { desc: 'ข้อมูลบริษัทในเครือ (เช่น MCO, ARICAT STUDIO, ARICAT LABOR, ARICAT DEVELOP)', group: 'ตั้งค่าระบบ' },
    'company_salary': { desc: 'ข้อมูลการจ่ายเงินเดือนและค่าตอบแทนแยกตามบริษัท', group: 'บุคคล & เงินเดือน' },
    'system_setting': { desc: 'การตั้งค่าทั่วไปของระบบ', group: 'ตั้งค่าระบบ' },
    'document_sequence': { desc: 'การจัดการรันเลขที่เอกสารอัตโนมัติของแต่ละประเภทเอกสารและแต่ละบริษัท', group: 'ตั้งค่าระบบ' },
    'document_revision': { desc: 'ประวัติการแก้ไขและเวอร์ชัน (Revision) ของเอกสาร เช่น PO, NPR', group: 'จัดการเอกสาร' },

    // 2. ฝ่ายบุคคลและพนักงาน
    'employee': { desc: 'ข้อมูลประวัติพนักงาน รูปถ่าย วันที่เริ่มงาน ตำแหน่ง และแผนก', group: 'บุคคล & องค์กร' },
    'department': { desc: 'แผนกงานในองค์กร', group: 'บุคคล & องค์กร' },
    'position': { desc: 'ตำแหน่งงานของพนักงาน', group: 'บุคคล & องค์กร' },
    'worker': { desc: 'ข้อมูลแรงงาน / คนงานภายนอกที่ร่วมปฏิบัติงาน', group: 'บุคคล & องค์กร' },
    'worker_doc': { desc: 'เอกสารแนบที่เกี่ยวข้องกับคนงาน/แรงงาน', group: 'บุคคล & องค์กร' },
    'employer': { desc: 'ข้อมูลนายจ้าง / ผู้ว่าจ้างแรงงาน', group: 'บุคคล & องค์กร' },
    'employer_doc': { desc: 'เอกสารแนบเกี่ยวกับนายจ้าง', group: 'บุคคล & องค์กร' },
    'agency': { desc: 'ข้อมูลตัวแทน / เอเจนซี่จัดหาแรงงาน', group: 'บุคคล & องค์กร' },
    'agency_doc': { desc: 'เอกสารแนบเกี่ยวกับตัวแทนเอเจนซี่', group: 'บุคคล & องค์กร' },

    // 3. ข้อมูลภูมิศาสตร์ / ที่อยู่
    'province': { desc: 'ข้อมูลจังหวัดในประเทศไทย', group: 'ที่อยู่ & ภูมิศาสตร์' },
    'amphur': { desc: 'ข้อมูลอำเภอ / เขตในประเทศไทย', group: 'ที่อยู่ & ภูมิศาสตร์' },
    'district': { desc: 'ข้อมูลตำบล / แขวง และรหัสไปรษณีย์ในประเทศไทย', group: 'ที่อยู่ & ภูมิศาสตร์' },
    'address_info': { desc: 'ข้อมูลที่อยู่สำหรับติดต่อของหน่วยงานหรือบุคคล', group: 'ที่อยู่ & ภูมิศาสตร์' },

    // 4. คลังสินค้าและสต๊อก
    'product': { desc: 'ข้อมูลสินค้าและบริการ (รหัสสินค้า, ชื่อสินค้า, ราคา, สต๊อก)', group: 'สินค้า & คลัง' },
    'product_group': { desc: 'กลุ่ม / หมวดหมู่สินค้า', group: 'สินค้า & คลัง' },
    'product_brand': { desc: 'ยี่ห้อ / แบรนด์ของสินค้า', group: 'สินค้า & คลัง' },
    'unit': { desc: 'หน่วยนับของสินค้า (เช่น ชิ้น, กล่อง, ชุด, อัน)', group: 'สินค้า & คลัง' },
    'warehouse': { desc: 'คลังสินค้าและสถานที่จัดเก็บ', group: 'สินค้า & คลัง' },
    'stock_sum': { desc: 'ยอดสินค้าคงเหลือสะสมในแต่ละคลังสินค้า', group: 'สินค้า & คลัง' },
    'stock_trans': { desc: 'รายการประวัติการเคลื่อนไหวสต๊อก (รับเข้า, เบิกออก, ย้ายคลัง, ปรับยอด)', group: 'สินค้า & คลัง' },
    'stock_monthly_snapshot': { desc: 'ภาพรวมยอดสต๊อกคงเหลือยกไปรายเดือน (Monthly Snapshot)', group: 'สินค้า & คลัง' },
    'journal_trans': { desc: 'เอกสารบันทึกการทำรายการสต๊อก (ใบรับสินค้าเข้าคลัง, ใบเบิกสินค้า)', group: 'สินค้า & คลัง' },
    'journal_trans_line': { desc: 'รายการสินค้าแต่ละบรรทัดในเอกสารบันทึกสต๊อก', group: 'สินค้า & คลัง' },
    'journal_trans_doc': { desc: 'เอกสารแนบของการทำรายการสต๊อก', group: 'สินค้า & คลัง' },
    'journal_trans_aricat': { desc: 'เอกสารรายการสต๊อกเฉพาะของกลุ่ม ARICAT', group: 'สินค้า & คลัง' },

    // 5. งานจัดซื้อ (Purchasing)
    'vendor': { desc: 'ข้อมูลผู้ขาย / ซัพพลายเออร์ / ผู้ให้บริการ (ชื่อ, เลขผู้เสียภาษี, เงื่อนไขเครดิต)', group: 'จัดซื้อ (Purchasing)' },
    'vendor_doc': { desc: 'เอกสารแนบเกี่ยวกับผู้ขาย (เช่น ภ.พ.20, หนังสือรับรองบริษัท)', group: 'จัดซื้อ (Purchasing)' },
    'purch_req': { desc: 'ใบขอซื้อสินค้า/บริการ (PR - Purchase Request)', group: 'จัดซื้อ (Purchasing)' },
    'purch_req_line': { desc: 'รายการสินค้าหรือบริการในใบขอซื้อ PR แต่ละรายการ', group: 'จัดซื้อ (Purchasing)' },
    'purch_req_doc': { desc: 'เอกสารแนบประกอบใบขอซื้อ PR (ใบเสนอราคา, สเปกสินค้า)', group: 'จัดซื้อ (Purchasing)' },
    'purch_req_reason': { desc: 'เหตุผลความจำเป็นในการขอซื้อสินค้า', group: 'จัดซื้อ (Purchasing)' },
    'purch_req_reason_title': { desc: 'หัวข้อเหตุผลการขอซื้อมาตรฐาน', group: 'จัดซื้อ (Purchasing)' },
    'purch_req_foot': { desc: 'หมายเหตุส่วนท้ายของใบขอซื้อ PR', group: 'จัดซื้อ (Purchasing)' },
    'purch_req_foot_title': { desc: 'ข้อความเงื่อนไขมาตรฐานท้ายใบขอซื้อ', group: 'จัดซื้อ (Purchasing)' },
    'purch': { desc: 'ใบสั่งซื้อสินค้า/บริการ (PO - Purchase Order)', group: 'จัดซื้อ (Purchasing)' },
    'purch_line': { desc: 'รายการสินค้าแต่ละบรรทัดในใบสั่งซื้อ PO', group: 'จัดซื้อ (Purchasing)' },
    'purch_doc': { desc: 'เอกสารแนบในใบสั่งซื้อ PO', group: 'จัดซื้อ (Purchasing)' },
    'purch_vendor_bill': { desc: 'ใบกำกับภาษี/บิลจากผู้ขายที่นำมาบันทึกผูกกับใบสั่งซื้อ PO', group: 'จัดซื้อ (Purchasing)' },
    'purch_receive_doc': { desc: 'เอกสารหลักฐานการรับสินค้าเข้าจากใบสั่งซื้อ', group: 'จัดซื้อ (Purchasing)' },
    'purch_payment': { desc: 'ข้อมูลการจ่ายชำระเงินค่าสินค้าตามใบสั่งซื้อ PO', group: 'จัดซื้อ (Purchasing)' },
    'purch_payment_line': { desc: 'รายละเอียดงวดการจ่ายเงินในใบสั่งซื้อ', group: 'จัดซื้อ (Purchasing)' },
    'purch_deposit': { desc: 'รายการมัดจำค่าสินค้าของใบสั่งซื้อ PO', group: 'จัดซื้อ (Purchasing)' },
    'purch_deposit_line': { desc: 'รายการสินค้าที่เกี่ยวข้องกับเงินมัดจำใบสั่งซื้อ', group: 'จัดซื้อ (Purchasing)' },
    'purchasemaster': { desc: 'บันทึกข้อมูลการซื้อสินค้าแบบไม่มีใบขอซื้อ (None PR Purchase Master)', group: 'จัดซื้อ (Purchasing)' },
    'purch_none_pr_doc': { desc: 'เอกสารแนบของการซื้อแบบไม่มีใบขอซื้อ (None PR)', group: 'จัดซื้อ (Purchasing)' },
    'purch_none_pr_deposit': { desc: 'เงินมัดจำสำหรับการซื้อแบบ None PR', group: 'จัดซื้อ (Purchasing)' },
    'purch_none_pr_deposit_line': { desc: 'รายการสินค้ามัดจำแบบ None PR', group: 'จัดซื้อ (Purchasing)' },
    'receiving_checklist': { desc: 'รายการตรวจสอบการตรวจรับสินค้าจากผู้ขาย', group: 'จัดซื้อ (Purchasing)' },

    // 6. การเงิน & บัญชี
    'chart_of_account': { desc: 'ผังบัญชี (Chart of Accounts) เลขที่บัญชีและชื่อบัญชี', group: 'การเงิน & บัญชี' },
    'account_category': { desc: 'หมวดหมู่ผังบัญชี (เช่น สินทรัพย์, หนี้สิน, ส่วนของผู้ถือหุ้น, รายได้, ค่าใช้จ่าย)', group: 'การเงิน & บัญชี' },
    'bank_account': { desc: 'ข้อมูลสมุดบัญชีเงินฝากธนาคารของบริษัท', group: 'การเงิน & บัญชี' },
    'payment_term': { desc: 'เงื่อนไขระยะเวลาการชำระเงิน (เช่น เงินสด, เครดิต 30 วัน, เครดิต 60 วัน)', group: 'การเงิน & บัญชี' },
    'payment_method': { desc: 'วิธีการชำระเงิน (เช่น โอนเงิน, เช็ค, เงินสด)', group: 'การเงิน & บัญชี' },
    'payment_extra_option': { desc: 'ตัวเลือกเพิ่มเติมในการจ่ายชำระเงิน', group: 'การเงิน & บัญชี' },
    'petty_cash_voucher': { desc: 'ใบสำคัญจ่ายเงินสดย่อย (Petty Cash Voucher)', group: 'การเงิน & บัญชี' },
    'petty_cash_voucher_doc': { desc: 'เอกสารแนบประกอบใบสำคัญเงินสดย่อย', group: 'การเงิน & บัญชี' },
    'petty_cash_voucher_doc_bill': { desc: 'ใบเสร็จรับเงิน/บิลเงินสดที่นำมาเบิกเงินสดย่อย', group: 'การเงิน & บัญชี' },
    'petty_cash_voucher_doc_slip': { desc: 'สลิปการโอนเงินคืนหรือจ่ายเงินสดย่อย', group: 'การเงิน & บัญชี' },
    'petty_cash_advance': { desc: 'ใบขอเบิกเงินสดย่อยทดแทน / เบิกเงินสำรองก้อนใหม่', group: 'การเงิน & บัญชี' },
    'petty_cash_detail': { desc: 'รายละเอียดรายการย่อยของการเบิกเงินสดย่อย', group: 'การเงิน & บัญชี' },
    'pre_advance': { desc: 'ใบขอเบิกเงินทดรองจ่ายล่วงหน้า (Payment Advance)', group: 'การเงิน & บัญชี' },
    'pre_advance_line': { desc: 'รายการวัตถุประสงค์ค่าใช้จ่ายที่ขอเบิกเงินทดรองจ่ายล่วงหน้า', group: 'การเงิน & บัญชี' },
    'pre_advance_doc': { desc: 'เอกสารแนบประกอบการเบิกเงินทดรองจ่าย', group: 'การเงิน & บัญชี' },
    'pre_advance_ref': { desc: 'เอกสารอ้างอิงของการเบิกเงินทดรองจ่าย', group: 'การเงิน & บัญชี' },
    'payment_voucher': { desc: 'ใบสำคัญจ่ายเงิน (Payment Voucher - PV)', group: 'การเงิน & บัญชี' },
    'payment_voucher_line': { desc: 'รายการบรรทัดค่าใช้จ่ายในใบสำคัญจ่าย PV', group: 'การเงิน & บัญชี' },
    'payment_voucher_doc': { desc: 'เอกสารแนบในใบสำคัญจ่าย PV', group: 'การเงิน & บัญชี' },
    'payment_voucher_ref': { desc: 'เอกสารที่นำมาอ้างอิงในการตั้งใบสำคัญจ่าย (เช่น PO, ใบแจ้งหนี้)', group: 'การเงิน & บัญชี' },
    'payment_attachment': { desc: 'ไฟล์แนบหลักฐานการชำระเงินหรือสลิปโอนเงิน', group: 'การเงิน & บัญชี' },
    'payment_receipt': { desc: 'ใบสำคัญรับเงิน / ใบรับชำระเงิน', group: 'การเงิน & บัญชี' },
    'payment_receipt_detail': { desc: 'รายละเอียดการรับชำระเงินแต่ละรายการ', group: 'การเงิน & บัญชี' },
    'billing_invoices': { desc: 'ใบวางบิล / เอกสารแจ้งยอดเรียกเก็บเงินจากลูกค้า', group: 'การเงิน & บัญชี' },
    'billing_invoice_items': { desc: 'รายการเอกสารหรือใบกำกับภาษีที่รวมอยู่ในใบวางบิล', group: 'การเงิน & บัญชี' },
    'invoice': { desc: 'ใบแจ้งหนี้ / ใบกำกับภาษี / ใบเสร็จรับเงิน (Invoices)', group: 'การเงิน & บัญชี' },
    'invoice_item': { desc: 'รายการสินค้าหรือบริการแต่ละรายการในใบแจ้งหนี้/ใบเสร็จ', group: 'การเงิน & บัญชี' },
    'invoice_doc': { desc: 'เอกสารแนบของใบแจ้งหนี้/ใบเสร็จรับเงิน', group: 'การเงิน & บัญชี' },
    'invoice_payment_history': { desc: 'ประวัติการรับชำระเงินของแต่ละใบแจ้งหนี้', group: 'การเงิน & บัญชี' },
    'invoice_payment_receipt': { desc: 'การผูกใบแจ้งหนี้เข้ากับใบรับเงิน', group: 'การเงิน & บัญชี' },
    'invoice_payment_extra': { desc: 'ค่าใช้จ่ายเพิ่มเติมหรือส่วนลดพิเศษตอนชำระเงิน', group: 'การเงิน & บัญชี' },
    'invoice_relation': { desc: 'ความสัมพันธ์และการเชื่อมโยงระหว่างเอกสารใบแจ้งหนี้', group: 'การเงิน & บัญชี' },
    'credit_note': { desc: 'ใบลดหนี้ (Credit Note - CN)', group: 'การเงิน & บัญชี' },
    'credit_note_item': { desc: 'รายการสินค้าหรือมูลค่าที่ลดหนี้ใน Credit Note', group: 'การเงิน & บัญชี' },
    'credit_note_doc': { desc: 'เอกสารแนบของใบลดหนี้', group: 'การเงิน & บัญชี' },
    'debit_note': { desc: 'ใบเพิ่มหนี้ (Debit Note - DN)', group: 'การเงิน & บัญชี' },
    'debit_note_item': { desc: 'รายการสินค้าหรือมูลค่าที่เพิ่มหนี้ใน Debit Note', group: 'การเงิน & บัญชี' },
    'debit_note_doc': { desc: 'เอกสารแนบของใบเพิ่มหนี้', group: 'การเงิน & บัญชี' },
    'wht': { desc: 'ข้อมูลหนังสือรับรองการหักภาษี ณ ที่จ่าย (Withholding Tax - ภ.ง.ด. 3, 53)', group: 'การเงิน & บัญชี' },
    'monthly_account_closing': { desc: 'บันทึกการปิดงวดบัญชีประจำเดือน', group: 'การเงิน & บัญชี' },
    'currency': { desc: 'สกุลเงินและอัตราแลกเปลี่ยน (เช่น THB, USD, EUR)', group: 'การเงิน & บัญชี' },

    // 7. งานขายและบริการ (Sales & Jobs)
    'customer': { desc: 'ข้อมูลลูกค้าและคู่ค้า (ชื่อ, ที่อยู่, เลขผู้เสียภาษี, เครดิตเทอม)', group: 'ขาย & บริการ' },
    'quotation': { desc: 'ใบเสนอราคา (Quotation - QT)', group: 'ขาย & บริการ' },
    'quotation_line': { desc: 'รายการสินค้าหรือบริการที่เสนอราคาในแต่ละบรรทัด', group: 'ขาย & บริการ' },
    'job': { desc: 'ใบสั่งงาน / ใบงานบริการ (Job Order)', group: 'ขาย & บริการ' },
    'job_line': { desc: 'รายการงานหรือสินค้าแต่ละรายการในใบงาน Job', group: 'ขาย & บริการ' },
    'job_expense': { desc: 'ค่าใช้จ่ายและต้นทุนที่เกิดขึ้นจริงในแต่ละใบงาน Job', group: 'ขาย & บริการ' },
    'job_contact_info': { desc: 'ข้อมูลผู้ติดต่อและสถานที่หน้างานของ Job', group: 'ขาย & บริการ' },
    'job_activity_status': { desc: 'สถานะความคืบหน้าของกิจกรรมในแต่ละใบงาน', group: 'ขาย & บริการ' },
    'job_doc_complete': { desc: 'เอกสารตรวจรับงานและส่งมอบงานที่เสร็จสิ้น', group: 'ขาย & บริการ' },
    'job_po_doc': { desc: 'เอกสารใบสั่งซื้อจากลูกค้าที่แนบใน Job', group: 'ขาย & บริการ' },
    'job_report_doc': { desc: 'รายงานการปฏิบัติงานและเอกสารแนบของ Job', group: 'ขาย & บริการ' },
    'delivery_note': { desc: 'ใบส่งของ / ใบส่งสินค้า / ใบตรวจรับงาน (Delivery Note)', group: 'ขาย & บริการ' },
    'delivery_note_line': { desc: 'รายการสินค้าแต่ละรายการในใบส่งของ Delivery Note', group: 'ขาย & บริการ' },
    'vehicle_expense': { desc: 'บันทึกค่าใช้จ่ายยานพาหนะและการเดินทาง (ค่าน้ำมัน, ค่าทางด่วน, ค่าซ่อมบำรุง)', group: 'ขาย & บริการ' },
    'driver_wage_report': { desc: 'รายงานคำนวณค่าแรงและค่าเที่ยวของพนักงานขับรถ', group: 'ขาย & บริการ' },

    // 8. OCR & อื่นๆ
    'ocr_pattern': { desc: 'รูปแบบและคำค้นหาสำหรับระบบรู้จำตัวอักษร OCR อัตโนมัติ', group: 'ระบบ AI & OCR' },
    'temp_invoice': { desc: 'ข้อมูลใบแจ้งหนี้ชั่วคราวจากการอ่านสแกน OCR', group: 'ระบบ AI & OCR' },
    'temp_invoice_line': { desc: 'รายการสินค้าชั่วคราวจากการสแกน OCR', group: 'ระบบ AI & OCR' }
};

// โหลดข้อมูลตารางเมื่อเปิด Modal
$('#dataDictModal').on('show.bs.modal', function() {
    loadDataDictionary();
});

function loadDataDictionary() {
    var tbody = $('#dict-table-body');
    if (dataDictList.length > 0) {
        renderDictTable(dataDictList);
        return;
    }

    tbody.html('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> กำลังโหลดข้อมูลตาราง...</td></tr>');

    $.ajax({
        url: {$dataDictUrlJson},
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            dataDictList = [];
            if (Array.isArray(res) && res.length > 0) {
                $.each(res, function(i, row) {
                    var tName = (row.TABLE_NAME || row.table_name || '').toLowerCase();
                    var dbComment = row.TABLE_COMMENT || row.table_comment || '';
                    var mapped = tableDescriptions[tName] || {};
                    var desc = mapped.desc || dbComment || 'ตารางเก็บข้อมูลระบบ ' + tName;
                    var grp = mapped.group || 'ตารางทั่วไปในระบบ';

                    dataDictList.push({
                        name: tName,
                        desc: desc,
                        group: grp
                    });
                });
            } else {
                // หากไม่สามารถดึงจาก DB ได้ ให้ใช้รายการจาก mapping มาตรฐาน
                $.each(tableDescriptions, function(tName, info) {
                    dataDictList.push({
                        name: tName,
                        desc: info.desc,
                        group: info.group
                    });
                });
            }
            renderDictTable(dataDictList);
        },
        error: function() {
            // กรณีเกิดข้อผิดพลาด ใช้ mapping เริ่มต้นทันที
            dataDictList = [];
            $.each(tableDescriptions, function(tName, info) {
                dataDictList.push({
                    name: tName,
                    desc: info.desc,
                    group: info.group
                });
            });
            renderDictTable(dataDictList);
        }
    });
}

function renderDictTable(list) {
    var tbody = $('#dict-table-body');
    tbody.empty();

    if (!list || list.length === 0) {
        tbody.append('<tr><td colspan="4" class="text-center py-3 text-muted">ไม่พบข้อมูลตาราง</td></tr>');
        return;
    }

    $.each(list, function(idx, item) {
        var tr = $('<tr></tr>');
        tr.append('<td class="text-center align-middle">' + (idx + 1) + '</td>');
        tr.append('<td class="align-middle font-weight-bold text-dark"><code>' + item.name + '</code></td>');
        tr.append('<td class="align-middle">' + item.desc + '</td>');
        tr.append('<td class="align-middle"><span class="badge badge-info">' + item.group + '</span></td>');
        tbody.append(tr);
    });
}

// ค้นหาในตาราง Data Dictionary
$('#filter-dict').on('keyup', function() {
    var kw = $(this).val().toLowerCase().trim();
    if (!kw) {
        renderDictTable(dataDictList);
        return;
    }
    var filtered = dataDictList.filter(function(item) {
        return item.name.toLowerCase().indexOf(kw) !== -1 || 
               item.desc.toLowerCase().indexOf(kw) !== -1 || 
               item.group.toLowerCase().indexOf(kw) !== -1;
    });
    renderDictTable(filtered);
});

// ฟังก์ชันคัดลอกตารางไปยังคลิปบอร์ด เพื่อนำไป Paste ลงใน Excel หรือ Word ได้ทันที
function copyDictTable() {
    var table = document.getElementById('dict-table');
    if (!table) return;

    var range = document.createRange();
    range.selectNode(table);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);

    try {
        var successful = document.execCommand('copy');
        window.getSelection().removeAllRanges();
        if (successful) {
            Swal.fire({
                icon: 'success',
                title: 'คัดลอกตารางแล้ว!',
                text: 'สามารถนำไปกดวาง (Ctrl+V) ลงใน Excel หรือ Word ได้ทันที',
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            fallbackCopyText();
        }
    } catch (err) {
        fallbackCopyText();
    }
}
window.copyDictTable = copyDictTable;

function fallbackCopyText() {
    var text = "ลำดับ\tชื่อตาราง (Table Name)\tคำอธิบาย\tหมวดหมู่งาน\n";
    $('#dict-table tbody tr').each(function() {
        var cols = [];
        $(this).find('td').each(function() {
            cols.push($(this).text().replace(/\t/g, ' ').trim());
        });
        if (cols.length >= 4) {
            text += cols.join('\t') + "\n";
        }
    });

    navigator.clipboard.writeText(text).then(function() {
        Swal.fire({
            icon: 'success',
            title: 'คัดลอกข้อมูลแล้ว!',
            text: 'สามารถนำไปกดวาง (Ctrl+V) ลงใน Excel หรือ Word ได้ทันที',
            timer: 2500,
            showConfirmButton: false
        });
    });
}

// ส่งออกเป็นไฟล์ CSV สำหรับเปิดด้วย Excel
function exportDictToCSV() {
    var csv = "\uFEFFลำดับ,ชื่อตาราง,คำอธิบาย,หมวดหมู่งาน\n";
    var idx = 1;
    $('#dict-table tbody tr').each(function() {
        var cols = [];
        $(this).find('td').each(function() {
            var val = $(this).text().replace(/"/g, '""').trim();
            cols.push('"' + val + '"');
        });
        if (cols.length >= 4) {
            csv += cols.join(',') + "\n";
        }
    });

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement("a");
    var url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "MCO_Data_Dictionary_Tables.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
window.exportDictToCSV = exportDictToCSV;
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>
