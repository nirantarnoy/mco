<?php

namespace backend\controllers;

use Yii;
use backend\models\Authitem;
use backend\models\AuthitemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AuthitemController - ปรับปรุงเพื่อรองรับการจัดการสิทธิ์แบบตาราง
 */
class AuthitemController extends Controller
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
                $this->updateRolePermissions($model->name, Yii::$app->request->post('permissions', []));
            }

            Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
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

                // กำหนด permissions สำหรับ Role ใหม่
                $this->updateRolePermissions($model->name, Yii::$app->request->post('permissions', []));

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
            return;
        }

        // ลบ permissions เดิมทั้งหมด
        $auth->removeChildren($role);

        // เพิ่ม permissions ใหม่
        foreach ($selectedPermissions as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if ($permission) {
                $auth->addChild($role, $permission);
            }
        }
    }

    /**
     * ดึงข้อมูลสำหรับแสดงในตาราง permissions
     * @param Authitem $model
     * @return array
     */
    protected function getPermissionsTableData($model)
    {
        $auth = Yii::$app->authManager;
        $allPermissions = $auth->getPermissions();

        // จัดกลุ่ม permissions ตาม module
        $modules = [];
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
                $currentPermissions[] = $perm->name;
            }
        }

        return [
            'modules' => $modules,
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
}