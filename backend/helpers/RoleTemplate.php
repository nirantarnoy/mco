<?php

namespace backend\helpers;

use Yii;

/**
 * RoleTemplate - เทมเพลตสำหรับสร้าง role ตามตำแหน่งงาน
 */
class RoleTemplate
{
    /**
     * รับเทมเพลตทั้งหมด
     */
    public static function getTemplates()
    {
        return [
            'admin' => [
                'name' => 'ผู้ดูแลระบบ',
                'description' => 'มีสิทธิ์เข้าถึงทุกฟังก์ชันในระบบ',
                'icon' => 'fas fa-user-shield',
                'color' => '#dc3545',
                'permissions' => 'all'
            ],
            'manager' => [
                'name' => 'ผู้จัดการ',
                'description' => 'สามารถดู อนุมัติ และจัดการข้อมูลส่วนใหญ่',
                'icon' => 'fas fa-user-tie',
                'color' => '#007bff',
                'permissions' => [
                    'categories' => ['sales', 'purchase', 'inventory', 'production', 'master', 'report'],
                    'actions' => ['index', 'view', 'create', 'update', 'approve', 'reject', 'print', 'export']
                ]
            ],
            'sales' => [
                'name' => 'ฝ่ายขาย',
                'description' => 'จัดการเอกสารการขายและข้อมูลลูกค้า',
                'icon' => 'fas fa-user-tag',
                'color' => '#28a745',
                'permissions' => [
                    'categories' => ['sales', 'master'],
                    'controllers' => ['quotation', 'salesOrder', 'invoice', 'receipt', 'billing', 'customer'],
                    'actions' => ['index', 'view', 'create', 'update', 'print', 'export']
                ]
            ],
            'purchase' => [
                'name' => 'ฝ่ายจัดซื้อ',
                'description' => 'จัดการเอกสารการซื้อและข้อมูลผู้จัดจำหน่าย',
                'icon' => 'fas fa-shopping-cart',
                'color' => '#17a2b8',
                'permissions' => [
                    'categories' => ['purchase', 'master'],
                    'controllers' => ['purch', 'purchReq', 'purchOrder', 'supplier'],
                    'actions' => ['index', 'view', 'create', 'update', 'print', 'export']
                ]
            ],
            'warehouse' => [
                'name' => 'พนักงานคลัง',
                'description' => 'จัดการสต็อกสินค้าและการเคลื่อนไหว',
                'icon' => 'fas fa-warehouse',
                'color' => '#ffc107',
                'permissions' => [
                    'categories' => ['inventory'],
                    'controllers' => ['product', 'warehouse', 'stockBalance', 'stockMovement', 'inventoryTrans'],
                    'actions' => ['index', 'view', 'create', 'update', 'print']
                ]
            ],
            'production' => [
                'name' => 'ฝ่ายผลิต',
                'description' => 'จัดการแผนการผลิตและใบงาน',
                'icon' => 'fas fa-industry',
                'color' => '#fd7e14',
                'permissions' => [
                    'categories' => ['production', 'inventory'],
                    'controllers' => ['productionPlan', 'job', 'workOrder', 'product'],
                    'actions' => ['index', 'view', 'create', 'update', 'print']
                ]
            ],
            'accountant' => [
                'name' => 'ฝ่ายบัญชี',
                'description' => 'ดูและตรวจสอบเอกสารทางการเงิน',
                'icon' => 'fas fa-calculator',
                'color' => '#6610f2',
                'permissions' => [
                    'categories' => ['sales', 'purchase', 'report'],
                    'controllers' => ['invoice', 'receipt', 'billing', 'purch', 'report'],
                    'actions' => ['index', 'view', 'print', 'export', 'approve']
                ]
            ],
            'viewer' => [
                'name' => 'ผู้ดูข้อมูล',
                'description' => 'สามารถดูข้อมูลเท่านั้น ไม่สามารถแก้ไข',
                'icon' => 'fas fa-eye',
                'color' => '#6c757d',
                'permissions' => [
                    'categories' => ['sales', 'purchase', 'inventory', 'production', 'master', 'report'],
                    'actions' => ['index', 'view', 'print', 'export']
                ]
            ],
            'data_entry' => [
                'name' => 'พนักงานบันทึกข้อมูล',
                'description' => 'บันทึกและแก้ไขข้อมูลพื้นฐาน',
                'icon' => 'fas fa-keyboard',
                'color' => '#20c997',
                'permissions' => [
                    'categories' => ['master'],
                    'controllers' => ['customer', 'supplier', 'employee', 'product'],
                    'actions' => ['index', 'view', 'create', 'update']
                ]
            ]
        ];
    }
    
    /**
     * สร้าง role จากเทมเพลต
     */
    public static function createRoleFromTemplate($templateKey, $customName = null)
    {
        $templates = self::getTemplates();
        
        if (!isset($templates[$templateKey])) {
            throw new \Exception("Template '{$templateKey}' not found");
        }
        
        $template = $templates[$templateKey];
        $auth = Yii::$app->authManager;
        
        $roleName = $customName ?? $templateKey;
        
        // ตรวจสอบว่า role มีอยู่แล้วหรือไม่
        $role = $auth->getRole($roleName);
        if ($role) {
            throw new \Exception("Role '{$roleName}' already exists");
        }
        
        // สร้าง role
        $role = $auth->createRole($roleName);
        $role->description = $template['description'];
        $auth->add($role);
        
        // เพิ่ม permissions
        $permissions = self::getPermissionsFromTemplate($template);
        
        foreach ($permissions as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if ($permission) {
                $auth->addChild($role, $permission);
            }
        }
        
        return $role;
    }
    
    /**
     * รับ permissions จากเทมเพลต
     */
    private static function getPermissionsFromTemplate($template)
    {
        if ($template['permissions'] === 'all') {
            // ให้สิทธิ์ทั้งหมด
            $auth = Yii::$app->authManager;
            $allPermissions = $auth->getPermissions();
            return array_keys($allPermissions);
        }
        
        $controllers = PermissionScanner::scanAllControllers();
        $permissions = [];
        
        foreach ($controllers as $controller) {
            // ตรวจสอบ category
            if (isset($template['permissions']['categories'])) {
                if (!in_array($controller['category'], $template['permissions']['categories'])) {
                    continue;
                }
            }
            
            // ตรวจสอบ controller
            if (isset($template['permissions']['controllers'])) {
                if (!in_array($controller['name'], $template['permissions']['controllers'])) {
                    continue;
                }
            }
            
            // เพิ่ม actions
            foreach ($controller['actions'] as $action) {
                if (isset($template['permissions']['actions'])) {
                    if (in_array($action['name'], $template['permissions']['actions'])) {
                        $permissions[] = PermissionScanner::createPermissionName($controller['name'], $action['name']);
                    }
                } else {
                    $permissions[] = PermissionScanner::createPermissionName($controller['name'], $action['name']);
                }
            }
        }
        
        return $permissions;
    }
    
    /**
     * รับจำนวน permissions ของแต่ละเทมเพลต
     */
    public static function getTemplatePermissionCount($templateKey)
    {
        $templates = self::getTemplates();
        
        if (!isset($templates[$templateKey])) {
            return 0;
        }
        
        $template = $templates[$templateKey];
        $permissions = self::getPermissionsFromTemplate($template);
        
        return count($permissions);
    }
}
