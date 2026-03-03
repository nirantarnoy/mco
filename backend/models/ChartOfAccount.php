<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "chart_of_account".
 *
 * @property int $id
 * @property string $account_code
 * @property string $account_name
 * @property string|null $account_group
 * @property int|null $account_level
 * @property int|null $account_type 1=Control, 2=Detail
 * @property int|null $parent_account_id
 * @property int|null $company_id
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property ChartOfAccount[] $chartOfAccounts
 * @property ChartOfAccount $parentAccount
 */
class ChartOfAccount extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chart_of_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account_group', 'parent_account_id', 'company_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['account_type'], 'default', 'value' => 2],
            [['account_code', 'account_name'], 'required'],
            [['account_level', 'account_type', 'parent_account_id', 'company_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['account_code'], 'string', 'max' => 20],
            [['account_name'], 'string', 'max' => 255],
            [['account_group'], 'string', 'max' => 50],
            [['account_code', 'company_id'], 'unique', 'targetAttribute' => ['account_code', 'company_id']],
            [['parent_account_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChartOfAccount::class, 'targetAttribute' => ['parent_account_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_code' => 'เลขที่บัญชี',
            'account_name' => 'ชื่อบัญชี',
            'account_group' => 'หมวด',
            'account_level' => 'ระดับ',
            'account_type' => 'ประเภท (1=ควม, 2=ย่อย)',
            'parent_account_id' => 'บัญชีคุม',
            'company_id' => 'Company ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[ChartOfAccounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChartOfAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, ['parent_account_id' => 'id']);
    }

    /**
     * Gets query for [[ParentAccount]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParentAccount()
    {
        return $this->hasOne(ChartOfAccount::class, ['id' => 'parent_account_id']);
    }

    public static function getAccountOptions()
    {
        $models = self::find()->orderBy(['account_code' => SORT_ASC])->all();
        $list = [];
        foreach ($models as $model) {
            $prefix = str_repeat('-- ', (int)$model->account_level - 1);
            $list[$model->id] = $prefix . $model->account_code . ' ' . $model->account_name;
        }
        return $list;
    }

    public function getAccountTypeName()
    {
        return $this->account_type == 1 ? 'คุม' : '---';
    }
}
