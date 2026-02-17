<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\helpers\PermissionScanner;
use backend\helpers\RoleTemplate;

/* @var $this yii\web\View */
/* @var $model backend\models\Authitem */

$this->title = $model->isNewRecord ? 'สร้าง Role ใหม่' : 'แก้ไข Role: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการสิทธิ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// ดึงข้อมูล controllers และ permissions (ใช้ cache เพื่อเพิ่มความเร็ว)
$cacheKey = 'permission_scanner_controllers';
$cacheDuration = 3600; // 1 ชั่วโมง

$controllers = Yii::$app->cache->getOrSet($cacheKey, function() {
    return PermissionScanner::scanAllControllers();
}, $cacheDuration);

$categories = PermissionScanner::getCategories();
$templates = RoleTemplate::getTemplates();

// จัดกลุ่ม controllers ตาม category
$groupedControllers = [];
foreach ($controllers as $controller) {
    $category = $controller['category'];
    if (!isset($groupedControllers[$category])) {
        $groupedControllers[$category] = [];
    }
    $groupedControllers[$category][] = $controller;
}

// ดึง permissions ปัจจุบัน
$currentPermissions = [];
if (!$model->isNewRecord && $model->type == 1) {
    $auth = Yii::$app->authManager;
    $role = $auth->getRole($model->name);
    if ($role) {
        $permissions = $auth->getPermissionsByRole($model->name);
        $currentPermissions = array_map('strtolower', array_keys($permissions));
    }
}
?>

<div class="authitem-form-new">
    <!-- Header Card -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield"></i>
                <?= Html::encode($this->title) ?>
            </h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-list"></i> รายการ', ['index'], ['class' => 'btn btn-sm btn-default']) ?>
                <?= Html::a('<i class="fas fa-sync"></i> รีเฟรช', ['update', 'id' => $model->name], ['class' => 'btn btn-sm btn-info']) ?>
            </div>
        </div>
    </div>

    <?php $form = \yii\widgets\ActiveForm::begin([
        'id' => 'authitem-form-new',
        'enableClientValidation' => true,
        'enableAjaxValidation' => false,
        'validateOnSubmit' => true,
    ]); ?>

    <!-- Hidden field for JSON payload (at top to ensure it's not truncated) -->
    <textarea name="permissions_json" id="permissions-json" style="display:none;"></textarea>

    <!-- Basic Information -->
    <div class="card">
        <div class="card-header bg-gradient-primary">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle"></i> ข้อมูลพื้นฐาน
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'name')->textInput([
                        'readonly' => !$model->isNewRecord,
                        'placeholder' => 'เช่น sales_manager'
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'type')->dropDownList([
                        1 => 'Role (บทบาท)',
                        2 => 'Permission (สิทธิ์)'
                    ], [
                        'disabled' => !$model->isNewRecord,
                        'id' => 'role-type'
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'description')->textInput([
                        'placeholder' => 'คำอธิบายบทบาท'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($model->type == 1): ?>
    
    <!-- Template Selection (for new roles only) -->
    <?php if ($model->isNewRecord): ?>
    <div class="card">
        <div class="card-header bg-gradient-info">
            <h5 class="card-title mb-0">
                <i class="fas fa-magic"></i> เลือกเทมเพลต (ไม่บังคับ)
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">เลือกเทมเพลตเพื่อตั้งค่าสิทธิ์เบื้องต้น หรือข้ามไปเพื่อกำหนดเอง</p>
            <div class="row" id="template-selection">
                <?php foreach ($templates as $key => $template): ?>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="template-card" data-template="<?= $key ?>">
                        <div class="template-icon" style="background-color: <?= $template['color'] ?>">
                            <i class="<?= $template['icon'] ?> fa-2x"></i>
                        </div>
                        <div class="template-info">
                            <h6><?= $template['name'] ?></h6>
                            <small class="text-muted"><?= $template['description'] ?></small>
                            <div class="mt-2">
                                <span class="badge badge-info">
                                    <?= RoleTemplate::getTemplatePermissionCount($key) ?> สิทธิ์
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Permission Management -->
    <div class="card">
        <div class="card-header bg-gradient-success">
            <h5 class="card-title mb-0">
                <i class="fas fa-key"></i> จัดการสิทธิ์การใช้งาน
            </h5>
            <div class="card-tools">
                <div class="permission-summary">
                    <span class="badge badge-light" id="selected-count">เลือกแล้ว: 0</span>
                    <span class="badge badge-light" id="total-count">ทั้งหมด: 0</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Control Panel -->
            <div class="control-panel mb-3">
                <div class="row">
                    <div class="col-md-8">
                        <div class="btn-toolbar" role="toolbar">
                            <div class="btn-group btn-group-sm mr-2" role="group">
                                <button type="button" class="btn btn-success" id="select-all">
                                    <i class="fas fa-check-double"></i> เลือกทั้งหมด
                                </button>
                                <button type="button" class="btn btn-warning" id="select-none">
                                    <i class="fas fa-times"></i> ยกเลิกทั้งหมด
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm mr-2" role="group">
                                <button type="button" class="btn btn-info" id="select-read">
                                    <i class="fas fa-eye"></i> อ่านอย่างเดียว
                                </button>
                                <button type="button" class="btn btn-primary" id="select-basic">
                                    <i class="fas fa-edit"></i> พื้นฐาน
                                </button>
                                <button type="button" class="btn btn-secondary" id="select-full">
                                    <i class="fas fa-crown"></i> เต็มรูปแบบ
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="expand-all">
                                    <i class="fas fa-expand-alt"></i> ขยายทั้งหมด
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="collapse-all">
                                    <i class="fas fa-compress-alt"></i> ย่อทั้งหมด
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="search-filter" placeholder="ค้นหาโมดูล...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress mb-3" style="height: 25px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                     role="progressbar" 
                     style="width: 0%" 
                     id="permission-progress">
                    <span id="progress-text">0%</span>
                </div>
            </div>

            <!-- Categories Filters (Using Pills instead of Tabs for filtering) -->
            <ul class="nav nav-pills mb-3" id="category-filters" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-filter="all" href="javascript:void(0)">
                        <i class="fas fa-th"></i> ทั้งหมด
                    </a>
                </li>
                <?php foreach ($categories as $catKey => $category): ?>
                <li class="nav-item">
                    <a class="nav-link" data-filter="<?= $catKey ?>" href="javascript:void(0)">
                        <i class="<?= $category['icon'] ?>"></i> 
                        <?= $category['label'] ?>
                        <span class="badge badge-secondary category-count" data-category="<?= $catKey ?>">0</span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <div id="permissions-container">
                <?php foreach ($groupedControllers as $catKey => $catControllers): ?>
                <div class="category-section" data-category="<?= $catKey ?>">
                    <h5 class="category-header" style="background-color: <?= $categories[$catKey]['color'] ?? '#6c757d' ?>">
                        <i class="<?= $categories[$catKey]['icon'] ?? 'fas fa-folder' ?>"></i>
                        <?= $categories[$catKey]['label'] ?? ucfirst($catKey) ?>
                        <span class="float-right d-flex align-items-center">
                            <small class="mr-2">เลือกทั้งหมวดพื้นฐาน</small>
                            <input type="checkbox" class="category-selector" data-category="<?= $catKey ?>">
                        </span>
                    </h5>
                    <div class="category-controllers">
                        <?= renderControllers($catControllers, $currentPermissions, $form) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <!-- Form Actions -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::submitButton(
                        '<i class="fas fa-save"></i> ' . ($model->isNewRecord ? 'สร้าง' : 'บันทึก'),
                        ['class' => 'btn btn-success btn-lg', 'id' => 'submit-btn']
                    ) ?>
                    <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
                <div class="col-md-6 text-right">
                    <?php if (!$model->isNewRecord): ?>
                    <?= Html::a('<i class="fas fa-eye"></i> ดูรายละเอียด', ['view', 'id' => $model->name], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->name], [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                        'data-method' => 'post'
                    ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JSON field moved to top -->

    <?php \yii\widgets\ActiveForm::end(); ?>
</div>

<?php
/**
 * Helper function to render controllers
 */
function renderControllers($controllers, $currentPermissions, $form) {
    $html = '<div class="accordion" id="controllers-accordion">';
    
    foreach ($controllers as $controller) {
        $controllerId = 'controller-' . $controller['name'];
        $isExpanded = false; // Default collapsed
        
        // นับจำนวน permissions ที่เลือก
        $selectedCount = 0;
        $totalCount = count($controller['actions']);
        
        foreach ($controller['actions'] as $action) {
            $permName = PermissionScanner::createPermissionName($controller['name'], $action['name']);
            if (in_array($permName, $currentPermissions)) {
                $selectedCount++;
            }
        }
        
        $html .= '<div class="card controller-card" data-controller="' . $controller['name'] . '">';
        $html .= '<div class="card-header" id="heading-' . $controllerId . '">';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<div class="flex-grow-1">';
        $html .= '<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#' . $controllerId . '">';
        $html .= '<i class="fas fa-chevron-right rotate-icon"></i> ';
        $html .= '<strong>' . Html::encode($controller['label']) . '</strong>';
        $html .= ' <small class="text-muted">(' . $controller['name'] . ')</small>';
        $html .= '</button>';
        if ($controller['description']) {
            $html .= '<br><small class="text-muted ml-4">' . Html::encode($controller['description']) . '</small>';
        }
        $html .= '</div>';
        $html .= '<div class="controller-stats">';
        $html .= '<span class="badge badge-info controller-count" data-controller="' . $controller['name'] . '">';
        $html .= $selectedCount . '/' . $totalCount;
        $html .= '</span>';
        $html .= '<input type="checkbox" class="ml-2 controller-selector" data-controller="' . $controller['name'] . '" title="เลือก/ยกเลิกทั้งหมด">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div id="' . $controllerId . '" class="collapse" data-parent="#controllers-accordion">';
        $html .= '<div class="card-body">';
        $html .= '<div class="row">';
        
        // จัดกลุ่ม actions ตามประเภท
        $actionsByType = [];
        foreach ($controller['actions'] as $action) {
            $type = $action['type'];
            if (!isset($actionsByType[$type])) {
                $actionsByType[$type] = [];
            }
            $actionsByType[$type][] = $action;
        }
        
        $typeLabels = [
            'read' => ['label' => 'อ่าน', 'color' => 'info'],
            'write' => ['label' => 'เขียน', 'color' => 'primary'],
            'delete' => ['label' => 'ลบ', 'color' => 'danger'],
            'export' => ['label' => 'ส่งออก', 'color' => 'success'],
            'approve' => ['label' => 'อนุมัติ', 'color' => 'warning'],
            'import' => ['label' => 'นำเข้า', 'color' => 'secondary'],
            'other' => ['label' => 'อื่นๆ', 'color' => 'dark']
        ];
        
        foreach ($actionsByType as $type => $actions) {
            $typeInfo = $typeLabels[$type] ?? $typeLabels['other'];
            
            $html .= '<div class="col-md-12 mb-3">';
            $html .= '<h6 class="text-' . $typeInfo['color'] . '">';
            $html .= '<i class="fas fa-tag"></i> ' . $typeInfo['label'];
            $html .= '</h6>';
            $html .= '<div class="action-group">';
            
            foreach ($actions as $action) {
                $permName = PermissionScanner::createPermissionName($controller['name'], $action['name']);
                $isChecked = in_array($permName, $currentPermissions);
                $isDangerous = $action['dangerous'];
                
                $html .= '<div class="custom-control custom-switch custom-control-inline action-item">';
                
                // ใช้ชื่อ id เป็นตัวเล็กทั้งหมดเพื่อให้ JS หาเจอได้ง่าย แต่ value ยังเป็นของเดิม
                $safeId = strtolower('perm-' . str_replace(['/', '-'], '_', $permName));
                
                $html .= Html::checkbox(
                    '', // Remove name to avoid max_input_vars limit
                    $isChecked,
                    [
                        'value' => $permName,
                        'class' => 'custom-control-input permission-checkbox' . ($isDangerous ? ' dangerous-action' : ''),
                        'id' => $safeId,
                        'data-controller' => $controller['name'],
                        'data-action' => $action['name'],
                        'data-type' => $type,
                        'uncheck' => null // สำคัญมาก: ไม่ให้ Yii สร้าง hidden field ซ้ำซ้อนสำหรับทุกตัว
                    ]
                );
                $html .= '<label class="custom-control-label" for="' . $safeId . '">';
                $html .= Html::encode($action['label']);
                if ($isDangerous) {
                    $html .= ' <i class="fas fa-exclamation-triangle text-danger" title="การกระทำที่อันตราย"></i>';
                }
                $html .= '</label>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    return $html;
}
?>

<style>
.authitem-form-new {
    padding: 20px 0;
}

.card {
    margin-bottom: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-header.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.card-header.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.card-header.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    color: white;
}

/* Template Cards */
.template-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 100%;
}

.template-card:hover {
    border-color: #007bff;
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
    transform: translateY(-5px);
}

.template-card.selected {
    border-color: #28a745;
    background-color: #d4edda;
}

.template-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: white;
}

.template-info h6 {
    font-weight: bold;
    margin-bottom: 5px;
}

/* Control Panel */
.control-panel {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

/* Category Header */
.category-header {
    padding: 10px 15px;
    color: white;
    border-radius: 5px;
    margin: 15px 0 10px 0;
    font-weight: bold;
}

.category-selector {
    transform: scale(1.3);
}

/* Controller Card */
.controller-card {
    margin-bottom: 10px;
    border-left: 4px solid #007bff;
}

.controller-card .card-header {
    background-color: #f8f9fa;
    padding: 10px 15px;
}

.controller-card .btn-link {
    color: #333;
    text-decoration: none;
    padding: 0;
    width: 100%;
    text-align: left;
}

.controller-card .btn-link:hover {
    color: #007bff;
}

.controller-card .rotate-icon {
    transition: transform 0.3s ease;
}

.controller-card .btn-link:not(.collapsed) .rotate-icon {
    transform: rotate(90deg);
}

.controller-stats {
    display: flex;
    align-items: center;
}

.controller-count {
    font-size: 0.9em;
    min-width: 50px;
    text-align: center;
}

.controller-selector {
    transform: scale(1.2);
    cursor: pointer;
}

/* Action Items */
.action-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.action-item {
    min-width: 200px;
}

.custom-control-label {
    cursor: pointer;
    user-select: none;
}

.dangerous-action + .custom-control-label {
    color: #dc3545;
    font-weight: bold;
}

/* Permission Summary */
.permission-summary .badge {
    font-size: 0.9em;
    margin-left: 5px;
}

/* Progress Bar */
#permission-progress {
    font-weight: bold;
}

/* Category Filters */
#category-filters {
    background-color: #fff;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

#category-filters .nav-link {
    border-radius: 20px;
    margin: 0 5px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

#category-filters .nav-link:hover {
    background-color: #f0f7ff;
    border-color: #007bff;
}

#category-filters .nav-link.active {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    box-shadow: 0 4px 10px rgba(0,123,255,0.3);
}

.category-section {
    margin-bottom: 30px;
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Controller Card Hover */
.controller-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    #category-filters {
        position: static;
        overflow-x: auto;
        white-space: nowrap;
        flex-wrap: nowrap;
    }
    
    #category-filters .nav-item {
        flex: 0 0 auto;
    }
}
    .btn-toolbar {
        flex-direction: column;
    }
    
    .btn-group {
        margin-bottom: 10px;
        width: 100%;
    }
    
    .btn-group .btn {
        flex: 1;
    }
    
    .action-item {
        min-width: 100%;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.controller-card {
    animation: fadeIn 0.3s ease;
}
</style>

<?php
$this->registerJs(<<<JS
$(document).ready(function() {
    // Initialize
    refreshUI();
    
    // Category Filtering
    $('#category-filters .nav-link').click(function(e) {
        e.preventDefault();
        var filter = $(this).data('filter');
        
        $('#category-filters .nav-link').removeClass('active');
        $(this).addClass('active');
        
        if (filter === 'all') {
            $('.category-section').show();
        } else {
            $('.category-section').hide();
            $('.category-section[data-category="' + filter + '"]').show();
        }
    });

    // Template Selection
    $('.template-card').click(function() {
        $('.template-card').removeClass('selected');
        $(this).addClass('selected');
        
        var template = $(this).data('template');
        applyTemplate(template);
    });
    
    // Bulk Action Handlers
    $('#select-all').click(function() {
        $('.permission-checkbox').prop('checked', true);
        refreshUI();
    });
    
    $('#select-none').click(function() {
        $('.permission-checkbox').prop('checked', false);
        $('.category-selector, .controller-selector').prop('checked', false);
        refreshUI();
    });
    
    $('#select-read').click(function() {
        $('.permission-checkbox').prop('checked', false);
        $('.permission-checkbox[data-type="read"]').prop('checked', true);
        refreshUI();
    });
    
    $('#select-basic').click(function() {
        $('.permission-checkbox').prop('checked', false);
        $('.permission-checkbox[data-type="read"], .permission-checkbox[data-type="write"]').prop('checked', true);
        refreshUI();
    });
    
    $('#select-full').click(function() {
        $('.permission-checkbox').prop('checked', true);
        $('.permission-checkbox.dangerous-action').prop('checked', false);
        refreshUI();
    });
    
    // Expand/Collapse All
    $('#expand-all').click(function() {
        $('.collapse').collapse('show');
    });
    
    $('#collapse-all').click(function() {
        $('.collapse').collapse('hide');
    });
    
    // Search Filter
    $('#search-filter').on('input', function() {
        var filter = $(this).val().toLowerCase();
        
        if (!filter) {
            $('.controller-card').show();
            return;
        }

        $('.controller-card').each(function() {
            var controller = $(this).data('controller').toLowerCase();
            var label = $(this).find('strong').text().toLowerCase();
            
            if (controller.includes(filter) || label.includes(filter)) {
                $(this).show();
                // Expand if searching
                $(this).find('.collapse').collapse('show');
            } else {
                $(this).hide();
            }
        });
    });
    
    // Category Selector
    $('.category-selector').change(function() {
        var category = $(this).data('category');
        var isChecked = $(this).is(':checked');
        $('.category-section[data-category="' + category + '"] .permission-checkbox').prop('checked', isChecked);
        $('.category-section[data-category="' + category + '"] .controller-selector').prop('checked', isChecked);
        refreshUI();
    });
    
    // Controller Selector
    $('.controller-selector').change(function() {
        var controller = $(this).data('controller');
        var isChecked = $(this).is(':checked');
        $('.permission-checkbox[data-controller="' + controller + '"]').prop('checked', isChecked);
        refreshUI();
    });
    
    // Permission Checkbox Change
    $(document).on('change', '.permission-checkbox', function() {
        updateStats();
        updateProgress();
        updateControllerCount($(this).data('controller'));
        updateCategoryCounts();
    });
    
    // Unified UI Refresh (Optimized)
    function refreshUI() {
        updateStats();
        updateProgress();
        updateCategoryCounts();
        
        // Update all controller counts
        $('.controller-card').each(function() {
            updateControllerCount($(this).data('controller'));
        });
    }

    function updateStats() {
        var total = $('.permission-checkbox').length;
        var selected = $('.permission-checkbox:checked').length;
        $('#selected-count').text('เลือกแล้ว: ' + selected);
        $('#total-count').text('ทั้งหมด: ' + total);
    }
    
    function updateProgress() {
        var total = $('.permission-checkbox').length;
        var selected = $('.permission-checkbox:checked').length;
        var percentage = total > 0 ? Math.round((selected / total) * 100) : 0;
        
        var progressBar = $('#permission-progress');
        progressBar.css('width', percentage + '%');
        $('#progress-text').text(percentage + '%');
        
        progressBar.removeClass('bg-danger bg-warning bg-success');
        if (percentage < 30) progressBar.addClass('bg-danger');
        else if (percentage < 70) progressBar.addClass('bg-warning');
        else progressBar.addClass('bg-success');
    }
    
    function updateControllerCount(controller) {
        var total = $('.permission-checkbox[data-controller="' + controller + '"]').length;
        var selected = $('.permission-checkbox[data-controller="' + controller + '"]:checked').length;
        $('.controller-count[data-controller="' + controller + '"]').text(selected + '/' + total);
        
        // Sync the controller selector checkbox
        var selector = $('.controller-selector[data-controller="' + controller + '"]');
        if (total > 0 && selected === total) selector.prop('checked', true);
        else if (selected === 0) selector.prop('checked', false);
    }
    
    function updateCategoryCounts() {
        $('.category-count').each(function() {
            var category = $(this).data('category');
            var checkboxes = $('.category-section[data-category="' + category + '"] .permission-checkbox');
            var total = checkboxes.length;
            var selected = checkboxes.filter(':checked').length;
            $(this).text(selected + '/' + total);
        });
    }
    
    function applyTemplate(template) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'กำลังใช้เทมเพลต...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        $.ajax({
            url: '/authitem/get-template-permissions',
            method: 'POST',
            data: { template: template },
            success: function(response) {
                if (response.success) {
                    $('.permission-checkbox').prop('checked', false);
                    response.permissions.forEach(function(perm) {
                        // Handle potential naming mismatches by converting to lowercase
                        var safeId = ('perm-' + perm.replace(/[\/\-]/g, '_')).toLowerCase();
                        $('#' + safeId).prop('checked', true);
                    });
                    refreshUI();
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'ปรับปรุงตามเทมเพลตแล้ว',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', response.message, 'error');
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                } else {
                    alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
                }
            }
        });
    }
    
    // Form Submit Handler
    var isSubmitting = false;
    $('#authitem-form-new').on('beforeSubmit', function(e) {
        if (isSubmitting) return false;
        
        // Gather all checked permissions into JSON to bypass max_input_vars limit
        var selectedPerms = [];
        $('.permission-checkbox:checked').each(function() {
            selectedPerms.push($(this).val());
        });
        $('#permissions-json').val(JSON.stringify(selectedPerms));
        
        isSubmitting = true;
        var submitBtn = $('#submit-btn');
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก ' + selectedPerms.length + ' รายการ...');
        submitBtn.prop('disabled', true);
        
        return true; 
    });
    
    $('#authitem-form-new').on('afterValidate', function(e, messages, errorAttributes) {
        if (errorAttributes.length > 0) {
            isSubmitting = false;
            var submitBtn = $('#submit-btn');
            submitBtn.html('<i class="fas fa-save"></i> บันทึก');
            submitBtn.prop('disabled', false);
        }
    });
});
JS
, \yii\web\View::POS_END);
?>
