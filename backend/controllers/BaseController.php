<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class BaseController extends Controller
{
    public function beforeAction($action)
    {
        // 1. ยกเว้นการตรวจสอบสำหรับ action สาธารณะของ SiteController
        if ($this->id === 'site' && in_array($action->id, ['login', 'error', 'logout', 'logindriver', 'logoutdriver'])) {
            return parent::beforeAction($action);
        }

        // 2. ตรวจสอบ Session และการล็อกอินพื้นฐาน
        if (!$this->isSessionValid()) {
            Yii::$app->user->logout();
            Yii::$app->response->redirect(['site/login'])->send();
            exit;
        }

        // 3. ตรวจสอบสิทธิ์การใช้งาน (RBAC Check)
        // สร้างชื่อ permission ตามรูปแบบเดียวกับ PermissionScanner: [controller]/[action] (ตัวเล็กทั้งหมด)
        $className = (new \ReflectionClass($this))->getShortName();
        $controllerName = strtolower(str_replace('Controller', '', $className));
        $actionName = strtolower(str_replace('-', '', $action->id));
        $permissionName = $controllerName . '/' . $actionName;

        if (!Yii::$app->user->can($permissionName)) {
            // อนุโลมให้เข้าหน้า Index ของระบบหลัก (Dashboard) ได้หากล็อกอินแล้ว
            if ($controllerName == 'site' && $actionName == 'index') {
                return parent::beforeAction($action);
            }
            
            throw new ForbiddenHttpException('คุณไม่ได้รับอนุญาตให้ใช้ส่วนนี้ (' . $permissionName . ')');
        }

        return parent::beforeAction($action);
    }

    /**
     * Checks if the current session is valid.
     *
     * @return bool
     */
    protected function isSessionValid(): bool
    {
        $session = Yii::$app->session;
        return !empty($session->get('company_id')) && !empty(Yii::$app->user->id);
    }
}
