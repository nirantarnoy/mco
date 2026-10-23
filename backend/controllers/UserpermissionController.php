<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\User;
use backend\models\Company;
use backend\models\ModulePermissionConfig;
use common\models\UserCompany;
use common\models\UserModulePermission;

class UserpermissionController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'save' => ['POST'],
                    'assign-companies' => ['POST'],
                    'switch-company' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * หน้าจอ Matrix จัดการสิทธิ์แยกตามบริษัท
     */
    public function actionIndex($company_id = null)
    {
        // สร้างตาราง user_company และ user_module_permission อัตโนมัติหากยังไม่มีใน DB
        $this->ensureTablesExist();

        $companies = Company::find()->where(['status' => 1])->all();
        if (empty($companies)) {
            $companies = Company::find()->all();
        }

        // ค่าเริ่มต้น company_id จาก session หรือ query หรือบริษัทแรก
        if ($company_id === null) {
            $company_id = Yii::$app->session->get('company_id');
            if (empty($company_id) || $company_id == 100) {
                $company_id = !empty($companies) ? $companies[0]->id : 1;
            }
        }
        $company_id = (int)$company_id;

        // ดึงรายชื่อ User ทั้งหมดที่มีสถานะใช้งาน (Active)
        $users = User::find()
            ->where(['status' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        // ตรวจสอบและซิงค์ว่า User แต่ละคนมีใน user_company หรือยัง (ถ้ายังไม่มี ให้ผูกกับบริษัทแรก)
        foreach ($users as $u) {
            $hasComp = UserCompany::find()->where(['user_id' => $u->id])->exists();
            if (!$hasComp && !empty($companies)) {
                $uc = new UserCompany();
                $uc->user_id = $u->id;
                $uc->company_id = $company_id;
                $uc->status = 1;
                $uc->created_at = time();
                $uc->save(false);
            }
        }

        // รายการบริษัทที่ User แต่ละคนมีสิทธิ์เข้าใช้งาน [user_id => [comp_id, comp_id, ...]]
        $userAssignedCompanies = [];
        $ucRows = UserCompany::find()->where(['status' => 1])->asArray()->all();
        foreach ($ucRows as $row) {
            $userAssignedCompanies[$row['user_id']][] = (int)$row['company_id'];
        }

        // รายการโมดูลทั้งหมด
        $modules = ModulePermissionConfig::getModuleList();

        // ค่าสิทธิ์ใน Matrix ของบริษัทนี้ [user_id => [module_key => 'x'|'0'|'1']]
        $currentPermissions = UserModulePermission::getPermissionsByCompany($company_id);

        return $this->render('index', [
            'companies' => $companies,
            'selectedCompanyId' => $company_id,
            'users' => $users,
            'modules' => $modules,
            'currentPermissions' => $currentPermissions,
            'userAssignedCompanies' => $userAssignedCompanies,
        ]);
    }

    /**
     * บันทึกการกำหนดสิทธิ์ Matrix
     */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $company_id = (int)Yii::$app->request->post('company_id');
        $matrixData = Yii::$app->request->post('matrix'); // [user_id => [module_key => 'x'|'0'|'1']]

        if (empty($company_id) || !is_array($matrixData)) {
            return [
                'status' => 'error',
                'message' => 'ข้อมูลไม่ถูกต้องหรือข้อมูลว่างเปล่า',
            ];
        }

        $now = time();
        $currentUserId = Yii::$app->user->id ?: 1;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($matrixData as $uId => $modPerms) {
                $uId = (int)$uId;
                if (empty($uId)) continue;

                foreach ($modPerms as $modKey => $pType) {
                    $pType = in_array($pType, ['x', '0', '1']) ? $pType : 'x';

                    $model = UserModulePermission::findOne([
                        'user_id' => $uId,
                        'company_id' => $company_id,
                        'module_key' => $modKey,
                    ]);

                    if (!$model) {
                        $model = new UserModulePermission();
                        $model->user_id = $uId;
                        $model->company_id = $company_id;
                        $model->module_key = $modKey;
                        $model->created_at = $now;
                    }

                    $model->permission_type = $pType;
                    $model->updated_at = $now;
                    $model->updated_by = $currentUserId;
                    $model->save(false);
                }

                // ซิงค์สิทธิ์เข้าสู่ระบบ RBAC ของ Yii2 ทันที
                ModulePermissionConfig::syncUserRbacPermissions($uId, $company_id, $modPerms);
            }

            $transaction->commit();

            return [
                'status' => 'success',
                'message' => 'บันทึกการกำหนดสิทธิ์และซิงค์เข้าสู่ระบบ RBAC เรียบร้อยแล้ว',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * บันทึกการมอบหมายบริษัทที่ User มีสิทธิ์เข้าใช้งาน
     */
    public function actionAssignCompanies()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = (int)Yii::$app->request->post('user_id');
        $companyIds = Yii::$app->request->post('company_ids', []);

        if (empty($userId)) {
            return ['status' => 'error', 'message' => 'ไม่พบรหัสผู้ใช้งาน'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // ลบการผูกบริษัทเดิม
            UserCompany::deleteAll(['user_id' => $userId]);

            if (is_array($companyIds)) {
                foreach ($companyIds as $cId) {
                    $cId = (int)$cId;
                    if ($cId > 0) {
                        $uc = new UserCompany();
                        $uc->user_id = $userId;
                        $uc->company_id = $cId;
                        $uc->status = 1;
                        $uc->created_at = time();
                        $uc->created_by = Yii::$app->user->id ?: 1;
                        $uc->save(false);
                    }
                }
            }

            $transaction->commit();
            return ['status' => 'success', 'message' => 'บันทึกบริษัทที่ได้รับสิทธิ์เรียบร้อย'];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ: ' . $e->getMessage()];
        }
    }

    /**
     * เปลี่ยนหรือสลับบริษัทที่กำลังทำงานอยู่
     */
    public function actionSwitchCompany($id)
    {
        $id = (int)$id;
        $userId = Yii::$app->user->id;

        if (!UserCompany::hasAccess($userId, $id) && !User::isUserAdmin($userId)) {
            Yii::$app->session->setFlash('msg-error', 'คุณไม่มีสิทธิ์เข้าถึงบริษัทนี้');
            return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
        }

        $company = Company::findOne($id);
        if ($company) {
            Yii::$app->session->set('company_id', $id);
            Yii::$app->session->set('company_name', $company->name);

            // โหลดสิทธิ์ของ User ในบริษัทใหม่นี้เข้า RBAC
            ModulePermissionConfig::applyCompanyPermissionsToUser($userId, $id);

            Yii::$app->session->setFlash('msg-success', 'สลับไปทำงานที่บริษัท ' . $company->name . ' เรียบร้อยแล้ว');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
    }

    /**
     * ฟังก์ชันตรวจสอบและสร้างตาราง user_company และ user_module_permission อัตโนมัติหากยังไม่มี
     */
    private function ensureTablesExist()
    {
        $db = Yii::$app->db;

        // ตรวจสอบตาราง user_company
        if ($db->getTableSchema('user_company') === null) {
            $sql = "CREATE TABLE IF NOT EXISTS `user_company` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `company_id` INT NOT NULL,
                `status` INT DEFAULT 1,
                `created_at` INT NULL,
                `created_by` INT NULL,
                UNIQUE KEY `uk_user_company` (`user_id`, `company_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $db->createCommand($sql)->execute();
        }

        // ตรวจสอบตาราง user_module_permission
        if ($db->getTableSchema('user_module_permission') === null) {
            $sql = "CREATE TABLE IF NOT EXISTS `user_module_permission` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `company_id` INT NOT NULL,
                `module_key` VARCHAR(64) NOT NULL,
                `permission_type` VARCHAR(10) DEFAULT 'x',
                `created_at` INT NULL,
                `updated_at` INT NULL,
                `updated_by` INT NULL,
                UNIQUE KEY `uk_user_comp_mod` (`user_id`, `company_id`, `module_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $db->createCommand($sql)->execute();
        }
    }
}
