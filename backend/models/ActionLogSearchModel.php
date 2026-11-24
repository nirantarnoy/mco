<?php
namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ActionLogSearchModel extends ActionLogModel
{
    public $date_from;
    public $date_to;
    public $user_search;

    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['username', 'action', 'controller', 'action_method', 'model_class', 'model_id',
                'ip_address', 'url', 'method', 'status', 'message', 'date_from', 'date_to', 'user_search'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = ActionLogModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'controller', $this->controller])
            ->andFilterWhere(['like', 'action_method', $this->action_method])
            ->andFilterWhere(['like', 'model_class', $this->model_class])
            ->andFilterWhere(['like', 'model_id', $this->model_id])
            ->andFilterWhere(['like', 'ip_address', $this->ip_address])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'method', $this->method])
            ->andFilterWhere(['like', 'message', $this->message]);

        // User search (รองรับทั้ง User ID, Username, และชื่อ-นามสกุลพนักงาน)
        if (!empty($this->user_search)) {
            $userTerm = trim($this->user_search);
            if (is_numeric($userTerm)) {
                $query->andFilterWhere(['user_id' => (int)$userTerm]);
            } else {
                $matchedUserIds = (new \yii\db\Query())
                    ->select('u.id')
                    ->from(['u' => 'user'])
                    ->leftJoin(['e' => 'employee'], 'e.id = u.emp_ref_id')
                    ->where(['like', 'u.username', $userTerm])
                    ->orWhere(['like', 'e.fname', $userTerm])
                    ->orWhere(['like', 'e.lname', $userTerm])
                    ->column();

                if (!empty($matchedUserIds)) {
                    $query->andWhere(['or',
                        ['like', 'username', $userTerm],
                        ['in', 'user_id', $matchedUserIds]
                    ]);
                } else {
                    $query->andFilterWhere(['like', 'username', $userTerm]);
                }
            }
        }

        // Date range filtering
        if (!empty($this->date_from)) {
            $query->andFilterWhere(['>=', 'created_at', $this->date_from . ' 00:00:00']);
        }

        if (!empty($this->date_to)) {
            $query->andFilterWhere(['<=', 'created_at', $this->date_to . ' 23:59:59']);
        }

        return $dataProvider;
    }

    public static function getPopularActions($limit = 20)
    {
        return ActionLogModel::find()
            ->select(['action', 'COUNT(*) as count'])
            ->groupBy('action')
            ->orderBy('count DESC')
            ->limit($limit)
            ->asArray()
            ->all();
    }

    public static function getPopularActionsList($limit = 30)
    {
        $actions = self::getPopularActions($limit);
        $list = [];
        foreach ($actions as $item) {
            $act = $item['action'];
            $list[$act] = $act . ' (' . number_format($item['count']) . ')';
        }
        return $list;
    }

    public static function getStatistics($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return [
            'total_logs' => ActionLogModel::find()->where(['>=', 'created_at', $date])->count(),
            'success_logs' => ActionLogModel::find()->where(['>=', 'created_at', $date])->andWhere(['status' => ActionLogModel::STATUS_SUCCESS])->count(),
            'failed_logs' => ActionLogModel::find()->where(['>=', 'created_at', $date])->andWhere(['status' => ActionLogModel::STATUS_FAILED])->count(),
            'warning_logs' => ActionLogModel::find()->where(['>=', 'created_at', $date])->andWhere(['status' => ActionLogModel::STATUS_WARNING])->count(),
            'unique_users' => ActionLogModel::find()->select('user_id')->distinct()->where(['>=', 'created_at', $date])->andWhere(['is not', 'user_id', null])->count(),
        ];
    }
}