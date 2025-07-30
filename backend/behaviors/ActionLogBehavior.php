<?php
namespace app\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\Controller;
use backend\models\ActionLog;

/**
 * ActionLogBehavior automatically logs model operations
 *
 * Usage in Model:
 * public function behaviors()
 * {
 *     return [
 *         ActionLogBehavior::class,
 *     ];
 * }
 *
 * Usage in Controller:
 * public function behaviors()
 * {
 *     return [
 *         'actionLog' => [
 *             'class' => ActionLogBehavior::class,
 *             'actions' => ['create', 'update', 'delete'], // เฉพาะ actions ที่ต้องการ log
 *         ],
 *     ];
 * }
 */
class ActionLogBehavior extends Behavior
{
    /**
     * @var array Actions to log (for Controller behavior)
     */
    public $actions = [];

    /**
     * @var bool Whether to log model operations
     */
    public $logModelOperations = true;

    /**
     * @var bool Whether to log controller actions
     */
    public $logControllerActions = true;

    /**
     * @var array Additional data to include in logs
     */
    public $additionalData = [];

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        $events = [];

        if ($this->owner instanceof ActiveRecord) {
            // Model events
            if ($this->logModelOperations) {
                $events[ActiveRecord::EVENT_AFTER_INSERT] = 'logModelInsert';
                $events[ActiveRecord::EVENT_AFTER_UPDATE] = 'logModelUpdate';
                $events[ActiveRecord::EVENT_AFTER_DELETE] = 'logModelDelete';
            }
        } elseif ($this->owner instanceof Controller) {
            // Controller events
            if ($this->logControllerActions) {
                $events[Controller::EVENT_BEFORE_ACTION] = 'logControllerAction';
            }
        }

        return $events;
    }

    /**
     * Log model insert operation
     */
    public function logModelInsert($event)
    {
        $model = $event->sender;
        $data = array_merge([
            'model_class' => get_class($model),
            'model_id' => $this->getModelId($model),
            'attributes' => $model->getAttributes(),
        ], $this->additionalData);

        ActionLog::log('MODEL_CREATE', $data, ActionLog::STATUS_SUCCESS, 'New ' . $this->getModelName($model) . ' created');
    }

    /**
     * Log model update operation
     */
    public function logModelUpdate($event)
    {
        $model = $event->sender;
        $changedAttributes = [];

        // ดึงข้อมูลที่เปลี่ยนแปลง
        foreach ($model->getDirtyAttributes() as $attribute => $newValue) {
            $oldValue = $model->getOldAttribute($attribute);
            $changedAttributes[$attribute] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        $data = array_merge([
            'model_class' => get_class($model),
            'model_id' => $this->getModelId($model),
            'changed_attributes' => $changedAttributes,
        ], $this->additionalData);

        ActionLog::log('MODEL_UPDATE', $data, ActionLog::STATUS_SUCCESS, $this->getModelName($model) . ' updated');
    }

    /**
     * Log model delete operation
     */
    public function logModelDelete($event)
    {
        $model = $event->sender;
        $data = array_merge([
            'model_class' => get_class($model),
            'model_id' => $this->getModelId($model),
            'deleted_attributes' => $model->getOldAttributes(),
        ], $this->additionalData);

        ActionLog::log('MODEL_DELETE', $data, ActionLog::STATUS_SUCCESS, $this->getModelName($model) . ' deleted');
    }

    /**
     * Log controller action
     */
    public function logControllerAction($event)
    {
        $controller = $event->sender;
        $action = $event->action;

        // ตรวจสอบว่าต้องการ log action นี้หรือไม่
        if (!empty($this->actions) && !in_array($action->id, $this->actions)) {
            return;
        }

        $request = Yii::$app->request;
        $data = array_merge([
            'controller_class' => get_class($controller),
            'action_id' => $action->id,
            'route' => $controller->route,
            'params' => $request->isPost ? $request->post() : $request->get(),
        ], $this->additionalData);

        // ลบข้อมูลสำคัญออก (เช่น password)
        if (isset($data['params']['password'])) {
            $data['params']['password'] = '[HIDDEN]';
        }
        if (isset($data['params']['password_repeat'])) {
            $data['params']['password_repeat'] = '[HIDDEN]';
        }

        ActionLog::log('CONTROLLER_ACTION', $data, ActionLog::STATUS_SUCCESS, "Executed {$controller->id}/{$action->id}");
    }

    /**
     * Get model ID (supports composite keys)
     */
    protected function getModelId($model)
    {
        $primaryKey = $model->getPrimaryKey();

        if (is_array($primaryKey)) {
            return json_encode($primaryKey);
        }

        return $primaryKey;
    }

    /**
     * Get friendly model name
     */
    protected function getModelName($model)
    {
        $className = get_class($model);
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Static method to manually log actions
     */
    public static function logAction($action, $data = [], $status = ActionLog::STATUS_SUCCESS, $message = null)
    {
        return ActionLog::log($action, $data, $status, $message);
    }

    /**
     * Log user login
     */
    public static function logLogin($user, $remember = false)
    {
        return ActionLog::log('USER_LOGIN', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'remember_me' => $remember,
        ], ActionLog::STATUS_SUCCESS, 'User logged in successfully');
    }

    /**
     * Log user logout
     */
    public static function logLogout($user)
    {
        return ActionLog::log('USER_LOGOUT', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
        ], ActionLog::STATUS_SUCCESS, 'User logged out');
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin($username, $reason = null)
    {
        return ActionLog::log('USER_LOGIN_FAILED', [
            'attempted_username' => $username,
            'reason' => $reason,
        ], ActionLog::STATUS_FAILED, 'Failed login attempt');
    }

    /**
     * Log permission denied
     */
    public static function logPermissionDenied($action, $resource = null)
    {
        return ActionLog::log('PERMISSION_DENIED', [
            'denied_action' => $action,
            'resource' => $resource,
        ], ActionLog::STATUS_WARNING, 'Access denied');
    }

    /**
     * Log file upload
     */
    public static function logFileUpload($filename, $size, $type)
    {
        return ActionLog::log('FILE_UPLOAD', [
            'filename' => $filename,
            'size' => $size,
            'type' => $type,
        ], ActionLog::STATUS_SUCCESS, 'File uploaded: ' . $filename);
    }

    /**
     * Log file download
     */
    public static function logFileDownload($filename, $path = null)
    {
        return ActionLog::log('FILE_DOWNLOAD', [
            'filename' => $filename,
            'path' => $path,
        ], ActionLog::STATUS_SUCCESS, 'File downloaded: ' . $filename);
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $details = [], $severity = ActionLog::STATUS_WARNING)
    {
        return ActionLog::log('SECURITY_EVENT', array_merge([
            'event_type' => $event,
        ], $details), $severity, 'Security event: ' . $event);
    }
}