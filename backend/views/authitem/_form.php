<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
function getActionLabel($action) {
    $labels = [
        'index' => 'ดูรายการ',
        'view' => 'ดูรายละเอียด',
        'create' => 'เพิ่มข้อมูล',
        'update' => 'แก้ไขข้อมูล',
        'delete' => 'ลบข้อมูล',
        'timeline' => 'ดู Timeline',
        'print' => 'พิมพ์',
        'export' => 'ส่งออก'
    ];
    return $labels[$action] ?? ucfirst($action);
}

function getModuleLabel($module) {
    $labels = [
        'job' => 'จัดการใบงาน',
        'customer' => 'จัดการลูกค้า',
        'product' => 'จัดการสินค้า',
        'purchase' => 'จัดการการสั่งซื้อ',
        'invoice' => 'จัดการใบกำกับภาษี',
        'billing' => 'จัดการใบวางบิล',
        'report' => 'รายงาน',
        'user' => 'จัดการผู้ใช้งาน',
        'usergroup' => 'จัดการกลุ่มผู้ใช้',
        'authitem' => 'จัดการสิทธิ์',
        'site' => 'ระบบหลัก'
    ];
    return $labels[$module] ?? ucfirst($module);
}

$modules = $permissionsData['modules'] ?? [];
$currentPermissions = $permissionsData['currentPermissions'] ?? [];
$standardActions = $permissionsData['standardActions'] ?? ['index', 'view', 'create', 'update', 'delete'];

// เพิ่ม actions อื่นๆ ที่อาจมีในระบบ
$allActions = array_unique(array_merge($standardActions, ['timeline', 'print', 'export', 'download', 'documents']));
?>

<div class="authitem-update">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield"></i>
                <?= Html::encode($this->title) ?>
            </h3>
            <div class="card-tools">
                <div class="btn-group">
                    <?= Html::a('<i class="fas fa-list"></i> รายการ', ['index'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::a('<i class="fas fa-plus"></i> เพิ่มใหม่', ['create'], ['class' => 'btn btn-sm btn-success']) ?>
                </div>
            </div>
        </div>

        <div class="card-body">
            <?php $form = ActiveForm::begin(['id' => 'authitem-form']); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['readonly' => !$model->isNewRecord]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'type')->dropDownList([
                        1 => 'Role (บทบาท)',
                        2 => 'Permission (สิทธิ์)'
                    ], ['disabled' => !$model->isNewRecord]) ?>
                </div>
            </div>

            <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

            <?php if ($model->type == 1): // แสดงตารางสิทธิ์เฉพาะ Role ?>
            <div class="permissions-section mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>
                        <i class="fas fa-table"></i>
                        การจัดการสิทธิ์การใช้งาน
                    </h5>
                    <div class="permission-stats">
                        <span class="badge badge-info" id="totalModules">โมดูล: <?= count($modules) ?></span>
                        <span class="badge badge-success" id="enabledPermissions">เปิดใช้: 0</span>
                        <span class="badge badge-secondary" id="totalPermissions">ทั้งหมด: 0</span>
                    </div>
                </div>

                <!-- Control Panel -->
                <div class="control-panel mb-3">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-success" id="selectAll">
                                    <i class="fas fa-check-square"></i> ทั้งหมด
                                </button>
                                <button type="button" class="btn btn-warning" id="selectNone">
                                    <i class="fas fa-square"></i> ยกเลิก
                                </button>
                                <button type="button" class="btn btn-info" id="selectIndex">
                                    <i class="fas fa-list"></i> Index
                                </button>
                                <button type="button" class="btn btn-primary" id="selectBasic">
                                    <i class="fas fa-eye"></i> พื้นฐาน
                                </button>
                                <button type="button" class="btn btn-dark" id="toggleAdvanced">
                                    <i class="fas fa-cog"></i> ขั้นสูง
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="moduleFilter" placeholder="กรองโมดูล...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="permissionsTable">
                        <thead class="thead-dark">
                            <tr>
                                <th width="20%" class="module-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Module</span>
                                        <input type="checkbox" id="selectAllModules" title="เลือก/ยกเลิกทั้งหมด">
                                    </div>
                                </th>
                                <?php foreach ($allActions as $action): ?>
                                <th class="text-center action-header" data-action="<?= $action ?>">
                                    <div class="action-column">
                                        <strong><?= ucfirst($action) ?></strong>
                                        <br><small style="color: white;"><?= getActionLabel($action) ?></small>
                                        <br><input type="checkbox" class="action-selector" data-action="<?= $action ?>" title="เลือกทั้งคอลัมน์">
                                    </div>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $moduleName => $moduleActions): ?>
                            <tr class="module-row" data-module="<?= $moduleName ?>">
                                <td class="module-cell">
                                    <div class="module-info">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= Html::encode($moduleName) ?></strong>
                                                <br><small class="text-muted"><?= getModuleLabel($moduleName) ?></small>
                                            </div>
                                            <div>
                                                <input type="checkbox" class="module-selector" data-module="<?= $moduleName ?>" title="เลือกทั้งแถว">
                                            </div>
                                        </div>
                                        <div class="module-stats mt-1">
                                            <span class="badge badge-sm badge-info module-count" data-module="<?= $moduleName ?>">0/0</span>
                                        </div>
                                    </div>
                                </td>
                                <?php foreach ($allActions as $action): ?>
                                <td class="text-center permission-cell" data-module="<?= $moduleName ?>" data-action="<?= $action ?>">
                                    <?php
                                    $permissionName = $moduleName . '/' . $action;
                                    $isChecked = in_array($permissionName, $currentPermissions);
                                    $exists = isset($moduleActions[$action]);
                                    ?>

                                    <?php if ($exists): ?>
                                        <div class="permission-control">
                                            <?= Html::checkbox(
                                                'permissions[]',
                                                $isChecked,
                                                [
                                                    'value' => $permissionName,
                                                    'class' => 'permission-checkbox',
                                                    'id' => 'perm_' . str_replace(['/', '-'], '_', $permissionName),
                                                    'data-module' => $moduleName,
                                                    'data-action' => $action
                                                ]
                                            ) ?>
                                            <label for="<?= 'perm_' . str_replace(['/', '-'], '_', $permissionName) ?>" class="permission-label">
                                                <span class="badge permission-status <?= $isChecked ? 'badge-success' : 'badge-secondary' ?>">
                                                    <?= $isChecked ? 'ON' : 'OFF' ?>
                                                </span>
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-summary">
                                <td><strong>สรุป</strong></td>
                                <?php foreach ($allActions as $action): ?>
                                <td class="text-center">
                                    <span class="badge badge-info action-summary" data-action="<?= $action ?>">0</span>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Progress Bar -->
                <div class="permissions-progress mt-3">
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="permissionProgress">
                            <span id="progressText">0%</span>
                        </div>
                    </div>
                    <small class="text-muted">ความคืบหน้าการกำหนดสิทธิ์</small>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <?= Html::submitButton($model->isNewRecord ? 'สร้าง' : 'บันทึก', [
                            'class' => 'btn btn-success btn-lg',
                            'id' => 'submit-btn'
                        ]) ?>
                        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <?php if (!$model->isNewRecord): ?>
                        <?= Html::a('ดูรายละเอียด', ['view', 'id' => $model->name], ['class' => 'btn btn-info']) ?>
                        <?= Html::a('ลบ', ['delete', 'id' => $model->name], [
                            'class' => 'btn btn-danger',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                            'data-method' => 'post'
                        ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
.permissions-section .table th {
    background-color: #343a40;
    color: white;
    text-align: center;
    vertical-align: middle;
    font-size: 0.85rem;
}

.permissions-section .table td {
    vertical-align: middle;
    padding: 8px;
}

.permission-checkbox {
    transform: scale(1.3);
    margin-right: 8px;
}

.permission-status {
    font-size: 0.7em;
    min-width: 35px;
    transition: all 0.3s ease;
}

.permission-control {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.permission-label {
    margin: 0;
    cursor: pointer;
}

.module-info {
    text-align: left;
}

.module-stats .badge {
    font-size: 0.6em;
}

.action-column {
    min-width: 80px;
}

.control-panel {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.permission-stats .badge {
    margin-left: 5px;
}

.permissions-progress .progress {
    height: 25px;
}

.module-row:hover {
    background-color: #f8f9fa;
}

.permission-cell:hover {
    background-color: #e3f2fd;
}

.table-summary {
    background-color: #f8f9fa;
    font-weight: bold;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }

    .control-panel .btn-group {
        flex-wrap: wrap;
        gap: 5px;
    }

    .permission-control {
        flex-direction: row;
    }

    .action-column {
        min-width: 60px;
    }
}

.module-cell {
    min-width: 200px;
}

.action-selector, .module-selector {
    transform: scale(1.2);
}

#moduleFilter {
    border-radius: 20px;
}

.fade-out {
    opacity: 0.3;
    transition: opacity 0.3s ease;
}
</style>

<?php
$js=<<<JS
$(document).ready(function() {
        // Initialize
        updateStats();
        updateProgress();

        // Checkbox change event
        $('.permission-checkbox').change(function() {
            var badge = $(this).siblings('label').find('.permission-status');
            if ($(this).is(':checked')) {
                badge.removeClass('badge-secondary').addClass('badge-success').text('ON');
            } else {
                badge.removeClass('badge-success').addClass('badge-secondary').text('OFF');
            }
            updateStats();
            updateProgress();
            updateModuleCount($(this).data('module'));
            updateActionCount($(this).data('action'));
        });

        // Select All
        $('#selectAll').click(function() {
            $('.permission-checkbox').prop('checked', true).trigger('change');
        });

        // Select None
        $('#selectNone').click(function() {
            $('.permission-checkbox').prop('checked', false).trigger('change');
        });

        // Select Index Only
        $('#selectIndex').click(function() {
            $('.permission-checkbox').prop('checked', false);
            $('input[data-action="index"]').prop('checked', true);
            $('.permission-checkbox').trigger('change');
        });

        // Select Basic (Index + View)
        $('#selectBasic').click(function() {
            $('.permission-checkbox').prop('checked', false);
            $('input[data-action="index"], input[data-action="view"]').prop('checked', true);
            $('.permission-checkbox').trigger('change');
        });

        // Module filter
        $('#moduleFilter').on('input', function() {
            var filter = $(this).val().toLowerCase();
            $('.module-row').each(function() {
                var moduleName = $(this).data('module').toLowerCase();
                var moduleLabel = $(this).find('.module-info strong').text().toLowerCase();
                if (moduleName.includes(filter) || moduleLabel.includes(filter)) {
                    $(this).show();
                } else {
                    $(this).addClass('fade-out');
                    setTimeout(() => $(this).hide(), 300);
                }
            });
        });

        // Action column selector
        $('.action-selector').change(function() {
            var action = $(this).data('action');
            var isChecked = $(this).is(':checked');
            $('input[data-action="' + action + '"]').prop('checked', isChecked).trigger('change');
        });

        // Module row selector
        $('.module-selector').change(function() {
            var module = $(this).data('module');
            var isChecked = $(this).is(':checked');
            $('input[data-module="' + module + '"]').prop('checked', isChecked).trigger('change');
        });

        // Select all modules checkbox
        $('#selectAllModules').change(function() {
            var isChecked = $(this).is(':checked');
            $('.module-selector').prop('checked', isChecked).trigger('change');
        });

        // Submit button animation
        $('#submit-btnx').click(function() {
            $(this).html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');
            $(this).prop('disabled', true);
        });

        // Functions
        function updateStats() {
            var total = $('.permission-checkbox').length;
            var enabled = $('.permission-checkbox:checked').length;

            $('#enabledPermissions').text('เปิดใช้: ' + enabled);
            $('#totalPermissions').text('ทั้งหมด: ' + total);
        }

        function updateProgress() {
            var total = $('.permission-checkbox').length;
            var enabled = $('.permission-checkbox:checked').length;
            var percentage = total > 0 ? Math.round((enabled / total) * 100) : 0;

            $('#permissionProgress').css('width', percentage + '%');
            $('#progressText').text(percentage + '%');

            // Change color based on percentage
            var progressBar = $('#permissionProgress');
            progressBar.removeClass('bg-danger bg-warning bg-success');
            if (percentage < 30) {
                progressBar.addClass('bg-danger');
            } else if (percentage < 70) {
                progressBar.addClass('bg-warning');
            } else {
                progressBar.addClass('bg-success');
            }
        }

        function updateModuleCount(module) {
            var total = $('input[data-module="' + module + '"]').length;
            var enabled = $('input[data-module="' + module + '"]:checked').length;
            $('.module-count[data-module="' + module + '"]').text(enabled + '/' + total);
        }

        function updateActionCount(action) {
            var enabled = $('input[data-action="' + action + '"]:checked').length;
            $('.action-summary[data-action="' + action + '"]').text(enabled);
        }

        // Initialize module and action counts
        $('.module-row').each(function() {
            var module = $(this).data('module');
            updateModuleCount(module);
        });

        $('.action-header').each(function() {
            var action = $(this).data('action');
            updateActionCount(action);
        });

        // Tooltips
        $('[title]').tooltip();

        // Advanced toggle (placeholder for future features)
        $('#toggleAdvanced').click(function() {
            // Toggle advanced features
            alert('ฟีเจอร์ขั้นสูงจะเพิ่มในอนาคต');
        });
    });
JS;
$this->registerJs($js,static::POS_END);
?>

