<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "monthly_account_closing".
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $year_month
 * @property float|null $petty_cash_balance
 * @property float|null $main_account_balance
 * @property string|null $statement_file
 * @property int|null $closed_by
 * @property int|null $closed_at
 * @property string|null $remarks
 */
class MonthlyAccountClosing extends ActiveRecord
{
    public static function tableName()
    {
        return 'monthly_account_closing';
    }

    public function rules()
    {
        return [
            [['year_month'], 'required'],
            [['company_id', 'closed_by', 'closed_at'], 'integer'],
            [['petty_cash_balance', 'main_account_balance'], 'number'],
            [['remarks'], 'string'],
            [['year_month'], 'string', 'max' => 10],
            [['statement_file'], 'string', 'max' => 255],
        ];
    }
}
