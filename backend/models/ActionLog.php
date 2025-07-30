<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * ActionLog model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $username
 * @property string $action
 * @property string $controller
 * @property string $action_method
 * @property string $model_class
 * @property string $model_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $url
 * @property string $method
 * @property string $data
 * @property string $status
 * @property string $message
 * @property string $created_at
 * @property string $updated_at
 */
class ActionLog extends ActiveRecord
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_WARNING = 'warning';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'action_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['action'], 'required'],
            [['user_id', 'model_id'], 'integer'],
            [['data', 'message', 'user_agent'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['username', 'model_class'], 'string', 'max' => 255],
            [['action'], 'string', 'max' => 255],
            [['controller', 'action_method'], 'string', 'max' => 100],
            [['ip_address'], 'string', 'max' => 45],
            [['url'], 'string', 'max' => 500],
            [['method'], 'string', 'max' => 10],
            [['status'], 'in', 'range' => [self::STATUS_SUCCESS, self::STATUS_FAILED, self::STATUS_WARNING]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'username' => 'Username',
            'action' => 'Action',
            'controller' => 'Controller',
            'action_method' => 'Action Method',
            'model_class' => 'Model Class',
            'model_id' => 'Model ID',
            'ip_address' => 'IP Address',
            'user_agent' => 'User Agent',
            'url' => 'URL',
            'method' => 'HTTP Method',
            'data' => 'Data',
            'status' => 'Status',
            'message' => 'Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * บันทึก Action Log
     */
    public static function log($action, $data = [], $status = self::STATUS_SUCCESS, $message = null)
    {
        $request = Yii::$app->request;
        $user = Yii::$app->user;

        // ดึงข้อมูล controller และ action จาก route
        $route = Yii::$app->controller ? Yii::$app->controller->route : '';
        $routeParts = explode('/', $route);
        $controller = isset($routeParts[0]) ? $routeParts[0] : null;
        $actionMethod = isset($routeParts[1]) ? $routeParts[1] : null;

        $log = new self([
            'user_id' => $user->isGuest ? null : $user->id,
            'username' => $user->isGuest ? 'Guest' : ($user->identity->username ?? 'Unknown'),
            'action' => $action,
            'controller' => $controller,
            'action_method' => $actionMethod,
            'ip_address' => $request->getUserIP(),
            'user_agent' => $request->getUserAgent(),
            'url' => $request->getUrl(),
            'method' => $request->getMethod(),
            'data' => !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            'status' => $status,
            'message' => $message,
        ]);

        // ถ้ามี model_class และ model_id ใน data
        if (isset($data['model_class'])) {
            $log->model_class = $data['model_class'];
        }
        if (isset($data['model_id'])) {
            $log->model_id = $data['model_id'];
        }

        return $log->save();
    }

    /**
     * บันทึก Model Action (Create, Update, Delete)
     */
    public static function logModelAction($action, $model, $data = [], $status = self::STATUS_SUCCESS, $message = null)
    {
        $modelData = array_merge($data, [
            'model_class' => get_class($model),
            'model_id' => $model->getPrimaryKey(),
        ]);

        return self::log($action, $modelData, $status, $message);
    }

    /**
     * ลบ log เก่าที่เกินจำนวนวันที่กำหนด
     */
    public static function cleanOldLogs($days = 90)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return self::deleteAll(['<', 'created_at', $date]);
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_WARNING => 'Warning',
        ];
    }

    /**
     * Get status label with color
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_SUCCESS => '<span class="label label-success">Success</span>',
            self::STATUS_FAILED => '<span class="label label-danger">Failed</span>',
            self::STATUS_WARNING => '<span class="label label-warning">Warning</span>',
        ];

        return isset($labels[$this->status]) ? $labels[$this->status] : $this->status;
    }

    /**
     * Get formatted data
     */
    public function getFormattedData()
    {
        if (empty($this->data)) {
            return null;
        }

        $data = json_decode($this->data, true);
        return $data ? $data : $this->data;
    }
}