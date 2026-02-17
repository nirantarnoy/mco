<?php
/**
 * ตัวอย่างการใช้งาน PermissionScanner และ RoleTemplate
 * 
 * ไฟล์นี้แสดงวิธีการใช้งาน helpers ต่างๆ ในระบบจัดการสิทธิ์
 */

use backend\helpers\PermissionScanner;
use backend\helpers\RoleTemplate;

// ===================================
// 1. การสแกน Controllers
// ===================================

// สแกนหา controllers ทั้งหมด
$controllers = PermissionScanner::scanAllControllers();

// แสดงผลลัพธ์
foreach ($controllers as $controller) {
    echo "Controller: {$controller['name']}\n";
    echo "Label: {$controller['label']}\n";
    echo "Category: {$controller['category']}\n";
    echo "Actions:\n";
    
    foreach ($controller['actions'] as $action) {
        echo "  - {$action['name']} ({$action['label']}) - Type: {$action['type']}\n";
        if ($action['dangerous']) {
            echo "    ⚠️ DANGEROUS ACTION!\n";
        }
    }
    echo "\n";
}

// ===================================
// 2. การซิงค์ Permissions
// ===================================

// ซิงค์ permissions กับฐานข้อมูล
$result = PermissionScanner::syncPermissions();

echo "Sync Results:\n";
echo "Created: {$result['created']}\n";
echo "Updated: {$result['updated']}\n";
echo "Total: {$result['total']}\n\n";

// ===================================
// 3. การใช้งาน Categories
// ===================================

$categories = PermissionScanner::getCategories();

foreach ($categories as $key => $category) {
    echo "Category: {$key}\n";
    echo "Label: {$category['label']}\n";
    echo "Icon: {$category['icon']}\n";
    echo "Color: {$category['color']}\n\n";
}

// ===================================
// 4. การสร้าง Permission Name
// ===================================

$permissionName = PermissionScanner::createPermissionName('customer', 'index');
echo "Permission Name: {$permissionName}\n"; // Output: customer/index

// ===================================
// 5. การใช้งาน Templates
// ===================================

// ดึงรายการ templates ทั้งหมด
$templates = RoleTemplate::getTemplates();

foreach ($templates as $key => $template) {
    echo "Template: {$key}\n";
    echo "Name: {$template['name']}\n";
    echo "Description: {$template['description']}\n";
    echo "Icon: {$template['icon']}\n";
    echo "Color: {$template['color']}\n";
    
    // นับจำนวน permissions
    $count = RoleTemplate::getTemplatePermissionCount($key);
    echo "Permissions Count: {$count}\n\n";
}

// ===================================
// 6. การสร้าง Role จาก Template
// ===================================

try {
    // สร้าง role สำหรับฝ่ายขาย
    $role = RoleTemplate::createRoleFromTemplate('sales', 'sales_staff');
    echo "Created role: {$role->name}\n";
    echo "Description: {$role->description}\n";
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

// ===================================
// 7. ตัวอย่างการกรอง Controllers
// ===================================

// กรองเฉพาะ controllers ในหมวด sales
$salesControllers = array_filter($controllers, function($controller) {
    return $controller['category'] === 'sales';
});

echo "Sales Controllers:\n";
foreach ($salesControllers as $controller) {
    echo "- {$controller['label']} ({$controller['name']})\n";
}

// ===================================
// 8. ตัวอย่างการกรอง Actions
// ===================================

// กรองเฉพาะ actions ประเภท read
foreach ($controllers as $controller) {
    $readActions = array_filter($controller['actions'], function($action) {
        return $action['type'] === 'read';
    });
    
    if (!empty($readActions)) {
        echo "{$controller['label']} - Read Actions:\n";
        foreach ($readActions as $action) {
            echo "  - {$action['label']}\n";
        }
    }
}

// ===================================
// 9. ตัวอย่างการสร้าง Custom Template
// ===================================

// สร้าง template แบบกำหนดเอง
$customTemplate = [
    'name' => 'พนักงานขายมือใหม่',
    'description' => 'สำหรับพนักงานขายที่เพิ่งเข้ามาใหม่',
    'icon' => 'fas fa-user-graduate',
    'color' => '#17a2b8',
    'permissions' => [
        'controllers' => ['customer', 'quotation'],
        'actions' => ['index', 'view', 'create']
    ]
];

// ในการใช้งานจริง ควรเพิ่มใน RoleTemplate::getTemplates()

// ===================================
// 10. ตัวอย่างการตรวจสอบ Dangerous Actions
// ===================================

echo "Dangerous Actions in System:\n";
foreach ($controllers as $controller) {
    foreach ($controller['actions'] as $action) {
        if ($action['dangerous']) {
            $permName = PermissionScanner::createPermissionName($controller['name'], $action['name']);
            echo "⚠️ {$permName} - {$controller['label']}/{$action['label']}\n";
        }
    }
}

// ===================================
// 11. สถิติระบบ
// ===================================

$totalControllers = count($controllers);
$totalActions = 0;
$totalPermissions = 0;

foreach ($controllers as $controller) {
    $totalActions += count($controller['actions']);
}

$auth = Yii::$app->authManager;
$allPermissions = $auth->getPermissions();
$totalPermissions = count($allPermissions);

echo "\nSystem Statistics:\n";
echo "Total Controllers: {$totalControllers}\n";
echo "Total Actions: {$totalActions}\n";
echo "Total Permissions in DB: {$totalPermissions}\n";

// ===================================
// 12. ตัวอย่างการใช้งานใน Controller
// ===================================

/*
// ในไฟล์ Controller ของคุณ

public function actionManagePermissions()
{
    // ดึงข้อมูล controllers
    $controllers = PermissionScanner::scanAllControllers();
    
    // ส่งไปยัง view
    return $this->render('manage-permissions', [
        'controllers' => $controllers
    ]);
}

public function actionSyncPermissions()
{
    $result = PermissionScanner::syncPermissions();
    
    Yii::$app->session->setFlash('success', 
        "ซิงค์เรียบร้อย: สร้างใหม่ {$result['created']} อัพเดท {$result['updated']}"
    );
    
    return $this->redirect(['index']);
}

public function actionCreateSalesRole()
{
    try {
        $role = RoleTemplate::createRoleFromTemplate('sales');
        Yii::$app->session->setFlash('success', "สร้าง role สำเร็จ");
        return $this->redirect(['view', 'id' => $role->name]);
    } catch (Exception $e) {
        Yii::$app->session->setFlash('error', $e->getMessage());
        return $this->redirect(['index']);
    }
}
*/

// ===================================
// 13. ตัวอย่างการใช้งานใน View
// ===================================

/*
// ในไฟล์ View ของคุณ

<?php
use backend\helpers\PermissionScanner;
use backend\helpers\RoleTemplate;

$controllers = PermissionScanner::scanAllControllers();
$categories = PermissionScanner::getCategories();
?>

<!-- แสดง controllers แยกตาม category -->
<?php foreach ($categories as $catKey => $category): ?>
    <h3><?= $category['label'] ?></h3>
    <?php foreach ($controllers as $controller): ?>
        <?php if ($controller['category'] === $catKey): ?>
            <div class="controller-item">
                <h4><?= $controller['label'] ?></h4>
                <ul>
                    <?php foreach ($controller['actions'] as $action): ?>
                        <li>
                            <?= $action['label'] ?>
                            <?php if ($action['dangerous']): ?>
                                <span class="badge badge-danger">อันตราย</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endforeach; ?>
*/

// ===================================
// 14. Advanced: การสร้าง Role แบบ Dynamic
// ===================================

/*
function createDynamicRole($roleName, $categoryFilter, $actionTypeFilter)
{
    $auth = Yii::$app->authManager;
    $controllers = PermissionScanner::scanAllControllers();
    
    // สร้าง role
    $role = $auth->createRole($roleName);
    $role->description = "Dynamic role for {$categoryFilter} - {$actionTypeFilter}";
    $auth->add($role);
    
    // เพิ่ม permissions
    foreach ($controllers as $controller) {
        if ($controller['category'] === $categoryFilter) {
            foreach ($controller['actions'] as $action) {
                if ($action['type'] === $actionTypeFilter) {
                    $permName = PermissionScanner::createPermissionName($controller['name'], $action['name']);
                    $permission = $auth->getPermission($permName);
                    if ($permission) {
                        $auth->addChild($role, $permission);
                    }
                }
            }
        }
    }
    
    return $role;
}

// ตัวอย่างการใช้งาน
$role = createDynamicRole('sales_viewer', 'sales', 'read');
*/

?>
