<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public function beforeAction($action)
    {
        // Skip validation for site controller's public actions
        if ($this->id === 'site' && in_array($action->id, ['login', 'error', 'logout', 'logindriver', 'logoutdriver'])) {
            return parent::beforeAction($action);
        }

        if (!$this->isSessionValid()) {
            Yii::$app->user->logout();
            Yii::$app->response->redirect(['site/login'])->send();
            exit;
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
