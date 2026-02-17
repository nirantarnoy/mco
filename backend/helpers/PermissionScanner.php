<?php

namespace backend\helpers;

use Yii;
use yii\helpers\FileHelper;
use ReflectionClass;
use ReflectionMethod;

/**
 * PermissionScanner - สแกนและจัดการ permissions อัตโนมัติ
 */
class PermissionScanner
{
    /**
     * สแกนหา controllers และ actions ทั้งหมดในระบบ
     * @return array
     */
    public static function scanAllControllers()
    {
        $controllersPath = Yii::getAlias('@backend/controllers');
        $controllers = [];
        
        $files = FileHelper::findFiles($controllersPath, [
            'only' => ['*Controller.php'],
            'recursive' => true
        ]);
        
        foreach ($files as $file) {
            $className = 'backend\\controllers\\' . basename($file, '.php');
            
            if (!class_exists($className)) {
                continue;
            }
            
            try {
                $reflection = new ReflectionClass($className);
                
                if ($reflection->isAbstract()) {
                    continue;
                }
                
                $controllerName = self::getControllerName($className);
                $actions = self::getControllerActions($reflection);
                
                if (!empty($actions)) {
                    $controllers[$controllerName] = [
                        'name' => $controllerName,
                        'label' => self::getControllerLabel($controllerName),
                        'className' => $className,
                        'actions' => $actions,
                        'category' => self::getControllerCategory($controllerName),
                        'description' => self::getControllerDescription($reflection)
                    ];
                }
            } catch (\Exception $e) {
                Yii::error("Error scanning controller {$className}: " . $e->getMessage());
            }
        }
        
        ksort($controllers);
        return $controllers;
    }
    
    /**
     * ดึง actions จาก controller
     * @param ReflectionClass $reflection
     * @return array
     */
    private static function getControllerActions(ReflectionClass $reflection)
    {
        $actions = [];
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            $methodName = $method->getName();
            
            // ตรวจสอบว่าเป็น action method หรือไม่
            if (strpos($methodName, 'action') === 0 && $methodName !== 'actions') {
                $actionName = lcfirst(substr($methodName, 6));
                
                $actions[$actionName] = [
                    'name' => $actionName,
                    'label' => self::getActionLabel($actionName),
                    'description' => self::getActionDescription($method),
                    'type' => self::getActionType($actionName),
                    'dangerous' => self::isDangerousAction($actionName)
                ];
            }
        }
        
        ksort($actions);
        return $actions;
    }
    
    /**
     * แปลง class name เป็น controller name
     */
    private static function getControllerName($className)
    {
        $baseName = basename(str_replace(['\\', '/'], '/', $className));
        $name = str_replace('Controller', '', $baseName);
        return strtolower($name);
    }
    
    /**
     * รับ label ของ controller
     */
    private static function getControllerLabel($controllerName)
    {
        $labels = [
            // ระบบหลัก
            'site' => 'ระบบหลัก',
            'dashboard' => 'แดชบอร์ด',
            
            // การขาย
            'quotation' => 'ใบเสนอราคา',
            'salesOrder' => 'ใบสั่งขาย',
            'invoice' => 'ใบกำกับภาษี',
            'receipt' => 'ใบเสร็จรับเงิน',
            'billing' => 'ใบวางบิล',
            
            // การซื้อ
            'purch' => 'ใบสั่งซื้อ',
            'purchReq' => 'ใบขอซื้อ',
            'purchOrder' => 'ใบสั่งซื้อ',
            
            // คลังสินค้า
            'product' => 'สินค้า',
            'warehouse' => 'คลังสินค้า',
            'stockBalance' => 'สต็อกสินค้า',
            'stockMovement' => 'การเคลื่อนไหวสต็อก',
            'inventoryTrans' => 'ทำรายการสต็อก',
            
            // การผลิต
            'productionPlan' => 'แผนการผลิต',
            'job' => 'ใบงาน',
            'workOrder' => 'ใบสั่งผลิต',
            
            // ข้อมูลหลัก
            'customer' => 'ลูกค้า',
            'supplier' => 'ผู้จัดจำหน่าย',
            'employee' => 'พนักงาน',
            'company' => 'บริษัท',
            
            // ระบบผู้ใช้
            'user' => 'ผู้ใช้งาน',
            'usergroup' => 'กลุ่มผู้ใช้',
            'authitem' => 'จัดการสิทธิ์',
            'authitemadvance' => 'จัดการสิทธิ์ขั้นสูง',
            
            // รายงาน
            'report' => 'รายงาน',
            'reportSales' => 'รายงานการขาย',
            'reportPurchase' => 'รายงานการซื้อ',
            'reportStock' => 'รายงานสต็อก',
        ];
        
        return $labels[$controllerName] ?? ucfirst($controllerName);
    }
    
    /**
     * รับ label ของ action
     */
    private static function getActionLabel($actionName)
    {
        $labels = [
            'index' => 'ดูรายการ',
            'view' => 'ดูรายละเอียด',
            'create' => 'เพิ่มข้อมูล',
            'update' => 'แก้ไขข้อมูล',
            'delete' => 'ลบข้อมูล',
            'bulkdelete' => 'ลบหลายรายการ',
            
            // การพิมพ์และส่งออก
            'print' => 'พิมพ์',
            'printOriginal' => 'พิมพ์ต้นฉบับ',
            'printCopy' => 'พิมพ์สำเนา',
            'export' => 'ส่งออก',
            'exportExcel' => 'ส่งออก Excel',
            'exportPdf' => 'ส่งออก PDF',
            'download' => 'ดาวน์โหลด',
            
            // การอนุมัติ
            'approve' => 'อนุมัติ',
            'reject' => 'ปฏิเสธ',
            'cancel' => 'ยกเลิก',
            'confirm' => 'ยืนยัน',
            
            // อื่นๆ
            'timeline' => 'ดู Timeline',
            'documents' => 'เอกสารแนบ',
            'history' => 'ประวัติ',
            'duplicate' => 'คัดลอก',
            'import' => 'นำเข้าข้อมูล',
            'search' => 'ค้นหา',
            'filter' => 'กรองข้อมูล',
            
            // รายงาน
            'report' => 'รายงาน',
            'summary' => 'สรุป',
            'detail' => 'รายละเอียด',
            
            // การตั้งค่า
            'settings' => 'ตั้งค่า',
            'config' => 'กำหนดค่า',
        ];
        
        return $labels[$actionName] ?? ucfirst($actionName);
    }
    
    /**
     * รับคำอธิบายของ action จาก docblock
     */
    private static function getActionDescription(ReflectionMethod $method)
    {
        $docComment = $method->getDocComment();
        if ($docComment) {
            // ดึงบรรทัดแรกของ docblock
            preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\n/s', $docComment, $matches);
            if (isset($matches[1])) {
                return trim($matches[1]);
            }
        }
        return '';
    }
    
    /**
     * รับคำอธิบายของ controller
     */
    private static function getControllerDescription(ReflectionClass $reflection)
    {
        $docComment = $reflection->getDocComment();
        if ($docComment) {
            preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\n/s', $docComment, $matches);
            if (isset($matches[1])) {
                return trim($matches[1]);
            }
        }
        return '';
    }
    
    /**
     * จัดหมวดหมู่ controller
     */
    private static function getControllerCategory($controllerName)
    {
        $categories = [
            'system' => ['site', 'dashboard', 'user', 'usergroup', 'authitem', 'authitemadvance'],
            'sales' => ['quotation', 'salesOrder', 'invoice', 'receipt', 'billing'],
            'purchase' => ['purch', 'purchReq', 'purchOrder'],
            'inventory' => ['product', 'warehouse', 'stockBalance', 'stockMovement', 'inventoryTrans'],
            'production' => ['productionPlan', 'job', 'workOrder'],
            'master' => ['customer', 'supplier', 'employee', 'company'],
            'report' => ['report', 'reportSales', 'reportPurchase', 'reportStock'],
        ];
        
        foreach ($categories as $category => $controllers) {
            if (in_array($controllerName, $controllers)) {
                return $category;
            }
        }
        
        return 'other';
    }
    
    /**
     * รับประเภทของ action
     */
    private static function getActionType($actionName)
    {
        $types = [
            'read' => ['index', 'view', 'search', 'filter', 'timeline', 'history'],
            'write' => ['create', 'update', 'duplicate'],
            'delete' => ['delete', 'bulkdelete'],
            'export' => ['print', 'printOriginal', 'printCopy', 'export', 'exportExcel', 'exportPdf', 'download'],
            'approve' => ['approve', 'reject', 'cancel', 'confirm'],
            'import' => ['import'],
        ];
        
        foreach ($types as $type => $actions) {
            if (in_array($actionName, $actions)) {
                return $type;
            }
        }
        
        return 'other';
    }
    
    /**
     * ตรวจสอบว่าเป็น action ที่อันตรายหรือไม่
     */
    private static function isDangerousAction($actionName)
    {
        $dangerousActions = ['delete', 'bulkdelete', 'cancel', 'reject'];
        return in_array($actionName, $dangerousActions);
    }
    
    /**
     * สร้าง permission name
     */
    public static function createPermissionName($controller, $action)
    {
        return strtolower($controller) . '/' . strtolower($action);
    }
    
    /**
     * รับหมวดหมู่ทั้งหมด
     */
    public static function getCategories()
    {
        return [
            'system' => [
                'label' => 'ระบบ',
                'icon' => 'fas fa-cog',
                'color' => '#6c757d'
            ],
            'sales' => [
                'label' => 'การขาย',
                'icon' => 'fas fa-shopping-cart',
                'color' => '#28a745'
            ],
            'purchase' => [
                'label' => 'การซื้อ',
                'icon' => 'fas fa-shopping-bag',
                'color' => '#17a2b8'
            ],
            'inventory' => [
                'label' => 'คลังสินค้า',
                'icon' => 'fas fa-warehouse',
                'color' => '#ffc107'
            ],
            'production' => [
                'label' => 'การผลิต',
                'icon' => 'fas fa-industry',
                'color' => '#fd7e14'
            ],
            'master' => [
                'label' => 'ข้อมูลหลัก',
                'icon' => 'fas fa-database',
                'color' => '#6610f2'
            ],
            'report' => [
                'label' => 'รายงาน',
                'icon' => 'fas fa-chart-bar',
                'color' => '#e83e8c'
            ],
            'other' => [
                'label' => 'อื่นๆ',
                'icon' => 'fas fa-ellipsis-h',
                'color' => '#6c757d'
            ]
        ];
    }
    
    /**
     * สร้าง permissions ในฐานข้อมูล
     */
    public static function syncPermissions()
    {
        $auth = Yii::$app->authManager;
        $controllers = self::scanAllControllers();
        $created = 0;
        $updated = 0;
        
        // ดึงสิทธิ์ทั้งหมดที่มีในระบบมาเทียบเคส
        $allExisting = $auth->getPermissions();
        $existingLowerMap = [];
        foreach ($allExisting as $name => $p) {
            $existingLowerMap[strtolower($name)] = $name;
        }
        
        foreach ($controllers as $controller) {
            foreach ($controller['actions'] as $action) {
                $permissionName = self::createPermissionName($controller['name'], $action['name']);
                $lowerName = strtolower($permissionName);
                
                // ตรวจสอบว่ามีอยู่แล้วหรือไม่ (แบบไม่สน Case)
                if (isset($existingLowerMap[$lowerName])) {
                    $originalName = $existingLowerMap[$lowerName];
                    $permission = $auth->getPermission($originalName);
                    
                    if ($permission) {
                        // อัพเดทคำอธิบาย
                        $permission->description = $controller['label'] . ' - ' . $action['label'];
                        $auth->update($originalName, $permission);
                        $updated++;
                    }
                } else {
                    // สร้างใหม่ (ใช้ตัวเล็กทั้งหมดเพื่อความเป็นเอกภาพ)
                    try {
                        $permission = $auth->createPermission($lowerName);
                        $permission->description = $controller['label'] . ' - ' . $action['label'];
                        $auth->add($permission);
                        $created++;
                        
                        // อัพเดท map ป้องกันการสร้างซ้ำในลูปเดียว
                        $existingLowerMap[$lowerName] = $lowerName;
                    } catch (\Exception $e) {
                        Yii::error("Sync Error creating {$lowerName}: " . $e->getMessage());
                    }
                }
            }
        }
        
        return [
            'created' => $created,
            'updated' => $updated,
            'total' => $created + $updated
        ];
    }
}
