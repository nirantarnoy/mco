<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../backend/config/main.php',
    require __DIR__ . '/../backend/config/main-local.php'
);

$application = new yii\web\Application($config);

echo "=======================================================\n";
echo "Database Table Summary & Expense Inspection\n";
echo "=======================================================\n\n";

// Check Company Salary table
$salaryCount = (new \yii\db\Query())->from('company_salary')->count();
$salarySum = (new \yii\db\Query())->from('company_salary')->sum('amount');
echo "1. company_salary table: Rows=$salaryCount, Total Sum=$salarySum\n";

// Check PO table
$poCount = (new \yii\db\Query())->from('purch')->where(['approve_status' => 1])->count();
$poSum = (new \yii\db\Query())->from('purch')->where(['approve_status' => 1])->sum('net_amount');
echo "2. purch table (Approved PO): Rows=$poCount, Total Sum=$poSum\n";

// Check None PR (purchase_master)
$nonePrCount = (new \yii\db\Query())->from('purchase_master')->where(['approve_status' => 1])->count();
$nonePrSum = (new \yii\db\Query())->from('purchase_master')->where(['approve_status' => 1])->sum('total_amount');
echo "3. purchase_master (None PR Approved): Rows=$nonePrCount, Total Sum=$nonePrSum\n";

// Check Petty Cash
$pettyCount = (new \yii\db\Query())->from('petty_cash_voucher')->where(['status' => 1])->count();
$pettySum = (new \yii\db\Query())->from('petty_cash_voucher')->where(['status' => 1])->sum('amount');
echo "4. petty_cash_voucher: Rows=$pettyCount, Total Sum=$pettySum\n";

// Check Vehicle Expenses
$veCount = (new \yii\db\Query())->from('vehicle_expense')->count();
$veCostSum = (new \yii\db\Query())->from('vehicle_expense')->sum('vehicle_cost');
$veWageSum = (new \yii\db\Query())->from('vehicle_expense')->sum('total_wage');
echo "5. vehicle_expense: Rows=$veCount, Cost Sum=$veCostSum, Wage Sum=$veWageSum\n";

// Check JournalTrans (other transactions)
$journalCount = (new \yii\db\Query())->from('journal_trans')->where(['status' => 2])->count();
echo "6. journal_trans (Approved): Rows=$journalCount\n";
$journalTypes = (new \yii\db\Query())
    ->select(['trans_type_id', 'COUNT(*) as cnt'])
    ->from('journal_trans')
    ->groupBy('trans_type_id')
    ->all();
foreach ($journalTypes as $jt) {
    echo "   -> Type ID {$jt['trans_type_id']}: {$jt['cnt']} rows\n";
}

// Check Job Revenues
$jobCount = (new \yii\db\Query())->from('job')->where(['status' => [1, 2]])->count();
$jobSum = (new \yii\db\Query())->from('job')->where(['status' => [1, 2]])->sum('job_amount');
echo "\n7. job table (Active Jobs): Rows=$jobCount, Total Revenue Sum=$jobSum\n";

echo "\n=======================================================\n";
