<?php

namespace backend\controllers;

use Yii;
use backend\models\Authitem;
use backend\models\AuthitemSearch;
use backend\helpers\PermissionScanner;
use backend\helpers\RoleTemplate;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AuthitemController - ปรับปรุงเพื่อรองรับการจัดการสิทธิ์แบบตาราง
 */
class AuthitemController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all Authitem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $pageSize = \Yii::$app->request->post("perpage", 20);
        $searchModel = new AuthitemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['type' => 1]); // เฉพาะ Roles
        $dataProvider->pagination->pageSize = $pageSize;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'perpage' => $pageSize,
        ]);
    }

    /**
     * Updates an existing Authitem model with table permissions
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $auth = Yii::$app->authManager;

        if ($model->load(Yii::$app->request->post())) {

            // ดึง permissions ผ่าน JSON เสมอ (เพื่อแก้ปัญหา max_input_vars)
            $jsonPermissions = Yii::$app->request->post('permissions_json');
            $receivedPermissions = !empty($jsonPermissions) ? json_decode($jsonPermissions, true) : [];
            if (!is_array($receivedPermissions)) $receivedPermissions = [];

            Yii::info('Processed permissions count: ' . count($receivedPermissions), 'authitem');
            Yii::info('Received permissions: ' . json_encode($receivedPermissions), 'authitem');

            // อัพเดทข้อมูลพื้นฐาน
            if ($model->type == 1) {
                $roleItem = $auth->getRole($model->name);
                $roleItem->description = $model->description;
            } else {
                $roleItem = $auth->getPermission($model->name);
                $roleItem->description = $model->description;
            }

            $auth->update($model->name, $roleItem);

            // จัดการ permissions สำหรับ Role
            if ($model->type == 1) {
                // ซิงค์สิทธิ์ใหม่ล่าสุดที่สแกนเจอเข้าไปใน DB ก่อน
                $syncResult = PermissionScanner::syncPermissions();
                
                $stats = $this->updateRolePermissions($model->name, $receivedPermissions);
                
                // เคลียร์ PHP Cache
                Yii::$app->cache->delete('permission_scanner_controllers');

                $msg = 'บันทึกบทบาทเรียบร้อยแล้ว<br>';
                $msg .= "- ได้รับสิทธิ์ที่เลือก: " . count($receivedPermissions) . " รายการ<br>";
                $msg .= "- เพิ่มใหม่: {$stats['added']} รายการ, ลบออก: {$stats['removed']} รายการ";
                if ($stats['errors'] > 0) $msg .= ", <span class='text-danger'>ผิดพลาด: {$stats['errors']} รายการ</span>";
                $msg .= "<br>- ซิงค์ระบบ: สร้างใหม่ {$syncResult['created']}, อัพเดท {$syncResult['updated']}";
                
                Yii::$app->session->setFlash('success', $msg);
            } else {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลพื้นฐานเรียบร้อยแล้ว');
            }
            return $this->redirect(['index']);
        }

        // ดึงข้อมูลสำหรับแสดงผล
        $permissionsData = $this->getPermissionsTableData($model);

        return $this->render('update', [
            'model' => $model,
            'permissionsData' => $permissionsData,
        ]);
    }

    /**
     * Creates a new Authitem model with table permissions support
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Authitem();
        $auth = Yii::$app->authManager;

        if ($model->load(Yii::$app->request->post())) {

            // สร้าง Role หรือ Permission ใหม่
            if ($model->type == 1) {
                $newRole = $auth->createRole($model->name);
                $newRole->description = $model->description;
                $auth->add($newRole);

                // กำหนด permissions ผ่าน JSON
                $jsonPermissions = Yii::$app->request->post('permissions_json');
                $perms = !empty($jsonPermissions) ? json_decode($jsonPermissions, true) : [];
                if (!is_array($perms)) $perms = [];
                $this->updateRolePermissions($model->name, $perms);

            } else {
                $newPermission = $auth->createPermission($model->name);
                $newPermission->description = $model->description;
                $auth->add($newPermission);
            }

            Yii::$app->session->setFlash('success', 'สร้างรายการเรียบร้อยแล้ว');
            return $this->redirect(['index']);
        }

        // ดึงข้อมูลสำหรับแสดงผล
        $permissionsData = $this->getPermissionsTableData($model);

        return $this->render('create', [
            'model' => $model,
            'permissionsData' => $permissionsData,
        ]);
    }

    /**
     * อัพเดท permissions ของ Role
     * @param string $roleName
     * @param array $selectedPermissions
     */
    protected function updateRolePermissions($roleName, $selectedPermissions = [])
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);

        if (!$role) {
            return ['added' => 0, 'removed' => 0, 'errors' => 1];
        }

        // 1. ดึงสิทธิ์ที่ระดับ "Direct" ของบทบาทนี้ (เพื่อเทียบว่าต้องเพิ่มหรือลบจริง)
        $directChildren = $auth->getChildren($roleName);
        $currentDirectLowerMap = [];
        foreach ($directChildren as $name => $child) {
            if ($child->type == \yii\rbac\Item::TYPE_PERMISSION) {
                $currentDirectLowerMap[strtolower($name)] = $name;
            }
        }
        
        // 2. เตรียมสิ่งที่ผู้ใช้เลือกมา (Lowercase เพื่อหาแมตช์)
        $selectedLowerMap = [];
        foreach ($selectedPermissions as $name) {
            if (empty($name)) continue;
            $selectedLowerMap[strtolower($name)] = $name;
        }

        $added = 0;
        $removed = 0;
        $errors = 0;

        // 3. จัดการ "ลบออก" (มีอยู่ใน DB แต่ไม่ได้เลือกมา)
        foreach ($currentDirectLowerMap as $lowerName => $originalName) {
            if (!isset($selectedLowerMap[$lowerName])) {
                $permission = $auth->getPermission($originalName);
                if ($permission) {
                    try {
                        $auth->removeChild($role, $permission);
                        $removed++;
                    } catch (\Exception $e) {
                        Yii::error("RBAC Remove Error: " . $e->getMessage());
                        $errors++;
                    }
                }
            }
        }

        // 4. จัดการ "เพิ่มเข้า" (เลือกมา แต่ยังไม่มีในระดับ Direct)
        // ดึงสิทธิ์ "ทั้งหมด" ในระบบเพื่อเอามาเทียบกรณี Case ไม่ตรง
        $allSystemPerms = $auth->getPermissions();
        $systemLowerMap = [];
        foreach ($allSystemPerms as $name => $p) {
            $systemLowerMap[strtolower($name)] = $p;
        }

        foreach ($selectedLowerMap as $lowerName => $inputName) {
            if (!isset($currentDirectLowerMap[$lowerName])) {
                // หาสิทธิ์ในระบบมาผูก
                $permission = $systemLowerMap[$lowerName] ?? null;

                // กรณีพิเศษ: ถ้าไม่มีสิทธิ์นี้ในระบบเลย (แม้แต่ใน DB) ให้สร้างใหม่
                if (!$permission) {
                    try {
                        $permission = $auth->createPermission($lowerName);
                        $permission->description = "Auto-created: " . $lowerName;
                        $auth->add($permission);
                        Yii::info("Auto-created missing permission: " . $lowerName);
                    } catch (\Exception $e) {
                        Yii::error("RBAC Create Error: " . $e->getMessage());
                    }
                }

                if ($permission) {
                    try {
                        if (!$auth->hasChild($role, $permission)) {
                            $auth->addChild($role, $permission);
                            $added++;
                        }
                    } catch (\Exception $e) {
                        Yii::error("RBAC Add Error: " . $e->getMessage());
                        $errors++;
                    }
                } else {
                    $errors++;
                }
            }
        }
        
        return ['added' => $added, 'removed' => $removed, 'errors' => $errors];
    }

    /**
     * ดึงข้อมูลสำหรับแสดงในตาราง permissions
     * @param Authitem $model
     * @return array
     */
    protected function getPermissionsTableData($model)
    {
        $auth = Yii::$app->authManager;
        
        // ใช้ PermissionScanner แทน
        $controllers = PermissionScanner::scanAllControllers();
        
        // จัดกลุ่ม permissions ตาม module (เก็บไว้เพื่อ backward compatibility)
        $modules = [];
        $allPermissions = $auth->getPermissions();
        foreach ($allPermissions as $permission) {
            $parts = explode('/', $permission->name);
            if (count($parts) == 2) {
                $module = $parts[0];
                $action = $parts[1];
                $modules[$module][$action] = [
                    'name' => $permission->name,
                    'description' => $permission->description
                ];
            }
        }

        // ดึง permissions ที่ role นี้มีอยู่แล้ว
        $currentPermissions = [];
        if ($model->type == 1 && !$model->isNewRecord) {
            $rolePermissions = $auth->getPermissionsByRole($model->name);
            foreach ($rolePermissions as $perm) {
                $currentPermissions[] = strtolower($perm->name);
            }
        }

        return [
            'modules' => $modules,
            'controllers' => $controllers,
            'currentPermissions' => $currentPermissions,
            'standardActions' => ['index', 'view', 'create', 'update', 'delete']
        ];
    }

    /**
     * Ajax action สำหรับดึงข้อมูล permissions ของ module
     * @return \yii\web\Response
     */
    public function actionGetModulePermissions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $moduleName = Yii::$app->request->post('module');
        $auth = Yii::$app->authManager;
        $permissions = $auth->getPermissions();

        $modulePermissions = [];
        foreach ($permissions as $permission) {
            if (strpos($permission->name, $moduleName . '/') === 0) {
                $action = str_replace($moduleName . '/', '', $permission->name);
                $modulePermissions[$action] = $permission->description;
            }
        }

        return ['success' => true, 'permissions' => $modulePermissions];
    }

    /**
     * Ajax action สำหรับ bulk update permissions
     * @return \yii\web\Response
     */
    public function actionBulkUpdatePermissions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $roleName = Yii::$app->request->post('roleName');
        $action = Yii::$app->request->post('action'); // 'selectAll', 'selectNone', etc.
        $auth = Yii::$app->authManager;

        $role = $auth->getRole($roleName);
        if (!$role) {
            return ['success' => false, 'message' => 'ไม่พบ Role ที่ระบุ'];
        }

        $allPermissions = $auth->getPermissions();
        $selectedPermissions = [];

        switch ($action) {
            case 'selectAll':
                foreach ($allPermissions as $permission) {
                    if (strpos($permission->name, '/') !== false) {
                        $selectedPermissions[] = $permission->name;
                    }
                }
                break;

            case 'selectIndex':
                foreach ($allPermissions as $permission) {
                    if (strpos($permission->name, '/index') !== false) {
                        $selectedPermissions[] = $permission->name;
                    }
                }
                break;

            case 'selectBasic':
                foreach ($allPermissions as $permission) {
                    if (strpos($permission->name, '/index') !== false ||
                        strpos($permission->name, '/view') !== false) {
                        $selectedPermissions[] = $permission->name;
                    }
                }
                break;

            case 'selectNone':
            default:
                $selectedPermissions = [];
                break;
        }

        $this->updateRolePermissions($roleName, $selectedPermissions);

        return [
            'success' => true,
            'message' => 'อัพเดทสิทธิ์เรียบร้อยแล้ว',
            'selectedPermissions' => $selectedPermissions
        ];
    }

    /**
     * สร้าง permissions เบื้องต้นสำหรับระบบ Job Management
     * @return mixed
     */
    public function actionInitJobPermissions()
    {
        $auth = Yii::$app->authManager;

        // ลบข้อมูลเดิม (ระวัง!)
        // $auth->removeAll(); // ใช้เฉพาะเมื่อต้องการรีเซ็ททั้งหมด

        $modules = [
            'job' => 'จัดการใบงาน',
            'customer' => 'จัดการลูกค้า',
            'product' => 'จัดการสินค้า',
            'purchase' => 'จัดการการสั่งซื้อ',
            'invoice' => 'จัดการใบกำกับภาษี',
            'report' => 'รายงาน',
            'user' => 'จัดการผู้ใช้งาน',
            'site' => 'ระบบหลัก'
        ];

        $actions = ['index', 'view', 'create', 'update', 'delete'];

        // สร้าง permissions สำหรับแต่ละ module
        foreach ($modules as $module => $moduleDesc) {
            foreach ($actions as $action) {
                $permissionName = $module . '/' . $action;

                // ตรวจสอบว่ามี permission นี้อยู่แล้วหรือไม่
                if (!$auth->getPermission($permissionName)) {
                    $permission = $auth->createPermission($permissionName);
                    $permission->description = $this->getActionDescription($action) . ' ' . $moduleDesc;
                    $auth->add($permission);
                }
            }
        }

        // สร้าง Role เบื้องต้น
        $roles = [
            'System Administrator' => 'ผู้ดูแลระบบ',
            'Procurement Manager' => 'ผู้จัดการฝ่ายจัดซื้อ',
            'Account Manager' => 'ผู้จัดการฝ่ายบัญชี',
            'User' => 'ผู้ใช้งานทั่วไป'
        ];

        foreach ($roles as $roleName => $roleDesc) {
            if (!$auth->getRole($roleName)) {
                $role = $auth->createRole($roleName);
                $role->description = $roleDesc;
                $auth->add($role);
            }
        }

        Yii::$app->session->setFlash('success', 'สร้าง Permissions และ Roles เบื้องต้นเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * รับคำอธิบาย action
     * @param string $action
     * @return string
     */
    protected function getActionDescription($action)
    {
        $descriptions = [
            'index' => 'ดูรายการ',
            'view' => 'ดูรายละเอียด',
            'create' => 'เพิ่มข้อมูล',
            'update' => 'แก้ไขข้อมูล',
            'delete' => 'ลบข้อมูล'
        ];

        return $descriptions[$action] ?? $action;
    }

    /**
     * Displays a single Authitem model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $permissionsData = $this->getPermissionsTableData($model);

        return $this->render('view', [
            'model' => $model,
            'permissionsData' => $permissionsData,
        ]);
    }

    /**
     * Deletes an existing Authitem model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $auth = Yii::$app->authManager;
        $item = $this->findModel($id);

        if ($item->type == 1) {
            $authItem = $auth->getRole($id);
        } else {
            $authItem = $auth->getPermission($id);
        }

        if ($authItem) {
            $auth->remove($authItem);
        }

        $item->delete();

        Yii::$app->session->setFlash('success', 'ลบรายการเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Authitem model based on its primary key value.
     * @param string $id
     * @return Authitem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Authitem::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบข้อมูลที่ต้องการ');
    }
    
    /**
     * สแกนและซิงค์ permissions อัตโนมัติ
     */
    public function actionSyncPermissions()
    {
        $result = PermissionScanner::syncPermissions();
        
        // เคลียร์ cache
        Yii::$app->cache->delete('permission_scanner_controllers');
        
        Yii::$app->session->setFlash('success', 
            "ซิงค์ permissions เรียบร้อยแล้ว<br>" .
            "สร้างใหม่: {$result['created']} รายการ<br>" .
            "อัพเดท: {$result['updated']} รายการ<br>" .
            "รวมทั้งหมด: {$result['total']} รายการ"
        );
        
        return $this->redirect(['index']);
    }
    
    /**
     * ดึงข้อมูล permissions จากเทมเพลต (Ajax)
     */
    public function actionGetTemplatePermissions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $templateKey = Yii::$app->request->post('template');
        $templates = RoleTemplate::getTemplates();
        
        if (!isset($templates[$templateKey])) {
            return ['success' => false, 'message' => 'Template not found'];
        }
        
        $template = $templates[$templateKey];
        
        // ดึง permissions ตามเทมเพลต
        $permissions = [];
        if ($template['permissions'] === 'all') {
            $auth = Yii::$app->authManager;
            $allPermissions = $auth->getPermissions();
            $permissions = array_keys($allPermissions);
        } else {
            // ใช้ logic จาก RoleTemplate
            $controllers = PermissionScanner::scanAllControllers();
            
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
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'permissions' => $permissions,
            'count' => count($permissions)
        ];
    }
    
    /**
     * สร้าง role จากเทมเพลต
     */
    public function actionCreateFromTemplate($template)
    {
        try {
            $role = RoleTemplate::createRoleFromTemplate($template);
            
            Yii::$app->session->setFlash('success', 
                "สร้าง Role '{$role->name}' จากเทมเพลตเรียบร้อยแล้ว"
            );
            
            return $this->redirect(['update', 'id' => $role->name]);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['index']);
        }
    }
    
    /**
     * เคลียร์ cache
     */
    public function actionClearCache()
    {
        Yii::$app->cache->delete('permission_scanner_controllers');
        
        Yii::$app->session->setFlash('success', 'เคลียร์ cache เรียบร้อยแล้ว');
        
        return $this->redirect(['index']);
    }
}
