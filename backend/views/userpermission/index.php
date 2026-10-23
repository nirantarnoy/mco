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

                <!-- ปุ่มบันทึก -->
                <div class="col-md-3 text-right">
                    <button type="button" class="btn btn-success btn-sm px-3 shadow" id="btn-save-all">
                        <i class="fas fa-save mr-1"></i> <strong>บันทึกการกำหนดสิทธิ์</strong>
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm ml-1" data-toggle="modal" data-target="#userCompanyModal" title="กำหนดสิทธิ์การเข้าถึงบริษัทของ User">
                        <i class="fas fa-users-cog mr-1"></i> ผูกสิทธิ์บริษัท
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
                        <!-- รายชื่อ User แต่ละคน -->
                        <?php foreach ($users as $user): ?>
                            <?php
                                $assignedComps = isset($userAssignedCompanies[$user->id]) ? $userAssignedCompanies[$user->id] : [];
                                $hasCompAccess = in_array($selectedCompanyId, $assignedComps) || \backend\models\User::isUserAdmin($user->id);
                            ?>
                            <th class="user-col-header <?= !$hasCompAccess ? 'bg-light text-muted' : '' ?>" data-user-id="<?= $user->id ?>">
                                <div class="font-weight-bold <?= $hasCompAccess ? 'text-primary' : 'text-muted' ?>">
                                    <?= Html::encode($user->username) ?>
                                </div>
                                <div class="small text-muted" style="font-size: 11px;">
                                    <?= \backend\models\User::findEmployeeNameByUserId($user->id) ?>
                                </div>
                                <?php if (!$hasCompAccess): ?>
                                    <span class="badge badge-secondary" style="font-size: 9px;">ไม่มีสิทธิ์บริษัทนี้</span>
                                <?php else: ?>
                                    <!-- ปุ่ม Quick Set ทั้งคอลัมน์ -->
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-xs btn-outline-danger bulk-action-btn" onclick="setColumnValue(<?= $user->id ?>, 'x')">x</button>
                                        <button type="button" class="btn btn-xs btn-outline-success bulk-action-btn" onclick="setColumnValue(<?= $user->id ?>, '0')">0</button>
                                        <button type="button" class="btn btn-xs btn-outline-info bulk-action-btn" onclick="setColumnValue(<?= $user->id ?>, '1')">1</button>
                                    </div>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedModules as $grpName => $grpMods): ?>
                        <?php $isApprovalGroup = (strpos($grpName, 'สิทธิ์การอนุมัติ') !== false); ?>
                        <tr class="group-header-row <?= $isApprovalGroup ? 'approval-group' : '' ?>">
                            <td colspan="<?= count($users) + 2 ?>" class="font-weight-bold">
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
                                <?php foreach ($users as $user): ?>
                                    <?php
                                        $val = isset($currentPermissions[$user->id][$modKey]) ? $currentPermissions[$user->id][$modKey] : 'x';
                                        $assignedComps = isset($userAssignedCompanies[$user->id]) ? $userAssignedCompanies[$user->id] : [];
                                        $hasCompAccess = in_array($selectedCompanyId, $assignedComps) || \backend\models\User::isUserAdmin($user->id);
                                    ?>
                                    <td class="<?= !$hasCompAccess ? 'bg-light' : '' ?>">
                                        <select class="form-control form-control-sm perm-select matrix-cell" 
                                                data-user-id="<?= $user->id ?>" 
                                                data-module-key="<?= $modKey ?>"
                                                data-val="<?= $val ?>"
                                                <?= !$hasCompAccess ? 'disabled' : '' ?>
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
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="saveUserCompany(<?= $u->id ?>)">
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

<?php
$saveUrl = Url::to(['userpermission/save']);
$assignUrl = Url::to(['userpermission/assign-companies']);
$selectedCompIdJson = json_encode($selectedCompanyId);

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
function setRowValue(modKey, value) {
    if (!value) return;
    $('.matrix-cell[data-module-key="' + modKey + '"]:not([disabled])').each(function() {
        $(this).val(value);
        updateCellColor(this);
    });
}

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
        url: '{$saveUrl}',
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

// บันทึกการผูกบริษัท
function saveUserCompany(userId) {
    var selectedCompIds = [];
    $('.user-comp-check-' + userId + ':checked').each(function() {
        selectedCompIds.push($(this).val());
    });

    $.ajax({
        url: '{$assignUrl}',
        type: 'POST',
        data: {
            user_id: userId,
            company_ids: selectedCompIds
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: res.message
                });
            }
        }
    });
}
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>
