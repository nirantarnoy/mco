<?php

namespace backend\controllers;

use app\behaviors\ActionLogBehavior;
use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;

/**
 * Site controller
 */
class SiteController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error','logindriver'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'changepassword','grab','logoutdriver','change-company','changecompany','google-auth','google-callback', 'view-file'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post', 'get'],
                ],
            ],
            'actionLog' => [
                'class' => ActionLogBehavior::class,
                'actions' => ['login', 'logout'], // Log เฉพาะ actions เหล่านี้
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */


    public function actionIndex()
    {
//        $job_no = 'QT25-000015';
//        $new_job_no ='';
//        if($job_no !=null){
//            $xp = explode("-", $job_no);
//            if(count($xp) == 3){
//                $new_job_no = $xp[1].'-'.$xp[2];
//            }else{
//                $new_job_no = $job_no;
//            }
//            echo count($xp);
//        }
//
//        echo $new_job_no;

        // รับค่าวันที่จาก request หรือใช้ค่า default (30 วันย้อนหลัง)
        $fromDate = Yii::$app->request->get('from_date', date('Y-m-d', strtotime('-30 days')));
        $toDate = Yii::$app->request->get('to_date', date('Y-m-d'));

        // แปลงวันที่เป็น timestamp
        $fromTimestamp = strtotime($fromDate);
        $toTimestamp = strtotime($toDate . ' 23:59:59');

        // 1. ยอดขายแยกตามสินค้า
        $salesByProduct = $this->getSalesByProduct($fromTimestamp, $toTimestamp);

        // 2. ข้อมูลสำหรับกราฟเปรียบเทียบราคาขายกับต้นทุน
        $priceComparisonData = $this->getPriceComparisonData($fromTimestamp, $toTimestamp);

        // 3. สินค้าขายดี 10 อันดับ
        $topProducts = $this->getTopProducts($fromTimestamp, $toTimestamp);

        return $this->render('index', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'salesByProduct' => $salesByProduct,
            'priceComparisonData' => $priceComparisonData,
            'topProducts' => $topProducts,
        ]);
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            //echo "login ok"; return;
            // return $this->goBack();
            $model_user_info = \backend\models\User::find()->where(['id' => \Yii::$app->user->id])->one();
            if($model_user_info){
                if($model_user_info->user_group_id == 3){
                    \Yii::$app->user->logout();
                }
            }
        //    return $this->redirect(['site/index']);
            // เก็บบริษัทไว้ใน session หลังจาก login สำเร็จ
            $login_company = \Yii::$app->request->post('login_company');
            $user_info = \backend\models\User::find()->where(['id' => \Yii::$app->user->id])->one();

            if ($login_company == 100) {
                if ($user_info && $user_info->user_group_id != 1) { // 1 is System Administrator
                    \Yii::$app->user->logout();
                    \Yii::$app->session->setFlash('msg-error', 'คุณไม่ได้รับอนุญาตให้เข้าถึงทุกบริษัท กรุณาเลือกบริษัทที่คุณสังกัด');
                    return $this->redirect(['site/login']);
                }
                $com_name = 'ทุกบริษัท';
            } else {
                $com_name = \backend\models\Company::findName($login_company);
            }

            Yii::$app->session->set('company_id', $login_company);
            Yii::$app->session->set('company_name', $com_name);
            return $this->redirect(['search/index']);
        }

        //   $model->password = '';
        $model->password = '';
        $this->layout = 'main_login';
        $model->password = '';
        return $this->render('login_new', [
            'model' => $model,
        ]);


    }




    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        \Yii::$app->user->logout();

//        if(isset($_SESSION['driver_login'])){
//            return $this->redirect(['site/logindriver']);
//        }

        return $this->goHome();
    }
    public function actionLogoutdriver()
    {
        \Yii::$app->user->logout();

        return $this->redirect(['site/logindriver']);
    }

    /**
     * Change current working company session dynamically.
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionChangeCompany($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $id = (int)$id;
        $user_info = \backend\models\User::find()->where(['id' => \Yii::$app->user->id])->one();

        if ($id == 100) {
            if ($user_info && $user_info->user_group_id != 1) {
                Yii::$app->session->setFlash('msg-error', 'คุณไม่ได้รับอนุญาตให้เข้าถึงทุกบริษัท');
                return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
            }
            $com_name = 'ทุกบริษัท';
        } else {
            $company = \backend\models\Company::findOne($id);
            if ($company) {
                $com_name = $company->name;
            } else {
                Yii::$app->session->setFlash('msg-error', 'ไม่พบข้อมูลบริษัทที่เลือก');
                return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
            }
        }

        Yii::$app->session->set('company_id', $id);
        Yii::$app->session->set('company_name', $com_name);

        return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
    }


    public function actionGoogleAuth()
    {
        $clientSecretPath = Yii::getAlias('@console/config/client_secret.json');
        if (!file_exists($clientSecretPath)) {
            Yii::$app->session->setFlash('msg-error', 'Client Secret file not found. Please upload it first.');
            return $this->redirect(['site/index']);
        }

        $client = new \Google\Client();
        $client->setAuthConfig($clientSecretPath);
        $client->addScope(\Google\Service\Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        
        // Use standard Yii2 URL generation for the callback
        $redirectUri = \yii\helpers\Url::to(['site/google-callback'], true);
        $client->setRedirectUri($redirectUri);

        $authUrl = $client->createAuthUrl();
        return $this->redirect($authUrl);
    }

    public function actionGoogleCallback()
    {
        $code = Yii::$app->request->get('code');
        if (!$code) {
            Yii::$app->session->setFlash('msg-error', 'Google Auth failed: No code received.');
            return $this->redirect(['site/index']);
        }

        $clientSecretPath = Yii::getAlias('@console/config/client_secret.json');
        $tokenPath = Yii::getAlias('@console/config/token.json');

        try {
            $client = new \Google\Client();
            $client->setAuthConfig($clientSecretPath);
            // Redirect URI must match exactly what was sent in actionGoogleAuth
            $redirectUri = \yii\helpers\Url::to(['site/google-callback'], true);
            $client->setRedirectUri($redirectUri);

            $accessToken = $client->fetchAccessTokenWithAuthCode($code);
            
            if (array_key_exists('error', $accessToken)) {
                throw new \Exception(join(', ', $accessToken));
            }

            $client->setAccessToken($accessToken);
            
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            
            Yii::$app->session->setFlash('msg-success', 'เชื่อมต่อ Google Drive สำเร็จแล้ว! (Token Generated)');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('msg-error', 'เชื่อมต่อล้มเหลว: ' . $e->getMessage());
        }

        return $this->redirect(['site/index']);
    }

    public function actionChangepassword()
    {
        $model = new \backend\models\Resetform();
        if ($model->load(Yii::$app->request->post())) {

            $model_user = \backend\models\User::find()->where(['id' => Yii::$app->user->id])->one();
            if ($model->oldpw != '' && $model->newpw != '' && $model->confirmpw != '') {
                if ($model->confirmpw != $model->newpw) {
                    $session = Yii::$app->session;
                    $session->setFlash('msg_err', 'รหัสยืนยันไม่ตรงกับรหัสใหม่');
                } else {
                    if ($model_user->validatePassword($model->oldpw)) {
                        $model_user->setPassword($model->confirmpw);
                        if ($model_user->save()) {
                            $session = Yii::$app->session;
                            $session->setFlash('msg_success', 'ทำการเปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
                            return $this->redirect(['site_/logout']);
                        }
                    } else {
                        $session = Yii::$app->session;
                        $session->setFlash('msg_err', 'รหัสผ่านเดิมไม่ถูกต้อง');
                    }
                }

            } else {
                $session = Yii::$app->session;
                $session->setFlash('msg_err', 'กรุณาป้อนข้อมูลให้ครบ');
            }

        }
        return $this->render('_setpassword', [
            'model' => $model
        ]);
    }

    public function actionGrab()
    {

        $aControllers = [];


        // $path = \Yii::$app->getBasePath() . 'icesystem/';
        $path = \Yii::$app->basePath;

        $ctrls = function ($path) use (&$ctrls, &$aControllers) {

            $oIterator = new \DirectoryIterator($path);

            foreach ($oIterator as $oFile) {

                if (!$oFile->isDot()

                    && (false !== strpos($oFile->getPathname(), 'controllers')

                        || false !== strpos($oFile->getPathname(), 'modules')

                    )

                ) {


                    if ($oFile->isDir()) {

                        $ctrls($oFile->getPathname());

                    } else {

                        if (strpos($oFile->getBasename(), 'Controller.php')) {


                            $content = file_get_contents($oFile->getPathname());

                            $controllerName = $oFile->getBasename('.php');


                            $route = explode(\Yii::$app->basePath, $oFile->getPathname());

                            $route = str_ireplace(array('modules', 'controllers', 'Controller.php'), '', $route[1]);

                            $route = preg_replace("/(\/){2,}/", "/", $route);


                            $aControllers[$controllerName] = [

                                'filepath' => $oFile->getPathname(),

                                'route' => mb_strtolower($route),

                                'actions' => [],

                            ];

                            preg_match_all('#function action(.*)\(#ui', $content, $aData);


                            $acts = function ($aData) use (&$aControllers, &$controllerName) {


                                if (!empty($aData) && isset($aData[1]) && !empty($aData[1])) {


                                    $aControllers[$controllerName]['actions'] = array_map(

                                        function ($actionName) {
                                            return mb_strtolower(trim($actionName, '{\\.*()'));
                                        },

                                        $aData[1]

                                    );


                                }

                            };


                            $acts($aData);

                        }

                    }


                }

            }

        };


        $ctrls($path);


        echo '<pre>';

        //   print_r($aControllers);

        foreach ($aControllers as $value) {

            //  $route_name = substr($value['route'],2);
            $route_name = substr($value['route'], 1);
            for ($x = 0; $x <= count($value['actions']) - 1; $x++) {
                $fullname = $route_name . '/' . $value['actions'][$x];
                if ($fullname != '') {
                    $fullname = str_replace('\\', '', $fullname);
                    $chk = \common\models\AuthItem::find()->where(['name' => $fullname])->one();
                    if ($chk) continue;

                    $model = new \common\models\AuthItem();
                    $model->name = $fullname;
                    $model->type = 2;
                    $model->description = '';
                    $model->created_at = time();
                    $model->save(false);
                }
                echo $fullname . '<br/>';

            }
            //echo $route_name;
            // print_r($value['route']);
        }
        // print_r($aControllers['AdjustmentController']);

    }

    /**
     * ดึงข้อมูลยอดขายแยกตามสินค้า
     */
    private function getSalesByProduct($fromTimestamp, $toTimestamp)
    {
        $query = (new Query())
            ->select([
                'p.id',
                'p.code',
                'p.name',
                'SUM(jtl.qty) as total_qty',
                'SUM(jtl.qty * jtl.line_price) as total_sales',
                'AVG(jtl.line_price) as avg_price',
                'AVG(p.cost_price) as cost_price',
                'SUM(jtl.qty * jtl.line_price) - SUM(jtl.qty * p.cost_price) as profit'
            ])
            ->from(['jtl' => 'journal_trans_line'])
            ->innerJoin(['p' => 'product'], 'jtl.product_id = p.id')
            ->innerJoin(['jt' => 'journal_trans'], 'jtl.journal_trans_id = jt.id')
            ->where(['between', 'jt.created_at', $fromTimestamp, $toTimestamp])
            ->andWhere(['jt.status' => 1,'jt.trans_type_id' => 3]) // สมมติว่า status 1 = ขายสำเร็จ
            ->groupBy(['p.id', 'p.code', 'p.name', 'p.cost_price'])
            ->orderBy(['total_sales' => SORT_DESC]);

        return $query->all();
    }

    /**
     * ดึงข้อมูลสำหรับกราฟเปรียบเทียบราคาขายกับต้นทุน
     */
    private function getPriceComparisonData($fromTimestamp, $toTimestamp)
    {
        $query = (new Query())
            ->select([
                'p.name',
                'p.cost_price',
                'AVG(jtl.line_price) as avg_sale_price',
                'SUM(jtl.qty) as total_qty'
            ])
            ->from(['jtl' => 'journal_trans_line'])
            ->innerJoin(['p' => 'product'], 'jtl.product_id = p.id')
            ->innerJoin(['jt' => 'journal_trans'], 'jtl.journal_trans_id = jt.id')
            ->where(['between', 'jt.created_at', $fromTimestamp, $toTimestamp])
            ->andWhere(['jt.status' => 1,'jt.trans_type_id' => 3])
            ->groupBy(['p.id', 'p.name', 'p.cost_price'])
            ->having('SUM(jt.qty) > 0')
            ->orderBy(['total_qty' => SORT_DESC])
            ->limit(20); // จำกัดแค่ 20 สินค้าสำหรับกราฟ

        $data = $query->all();

        // จัดรูปแบบข้อมูลสำหรับ Highcharts
        $categories = [];
        $costPrices = [];
        $salePrices = [];
        $profits = [];

        foreach ($data as $item) {
            $categories[] = $item['name'];
            $costPrices[] = floatval($item['cost_price']);
            $salePrices[] = floatval($item['avg_sale_price']);
            $profits[] = floatval($item['avg_sale_price']) - floatval($item['cost_price']);
        }

        return [
            'categories' => $categories,
            'costPrices' => $costPrices,
            'salePrices' => $salePrices,
            'profits' => $profits
        ];
    }

    /**
     * ดึงข้อมูลสินค้าขายดี 10 อันดับ
     */
    private function getTopProducts($fromTimestamp, $toTimestamp)
    {
        $query = (new Query())
            ->select([
                'p.name',
                'p.code',
                'SUM(jtl.qty) as total_qty',
                'SUM(jtl.qty * jtl.line_price) as total_sales'
            ])
            ->from(['jtl' => 'journal_trans_line'])
            ->innerJoin(['p' => 'product'], 'jtl.product_id = p.id')
            ->innerJoin(['jt' => 'journal_trans'], 'jtl.journal_trans_id = jt.id')
            ->where(['between', 'jt.created_at', $fromTimestamp, $toTimestamp])
            ->andWhere(['jt.status' => 1,'jt.trans_type_id' => 3])
            ->groupBy(['p.id', 'p.name', 'p.code'])
            ->orderBy(['total_qty' => SORT_DESC])
            ->limit(10);

        $data = $query->all();

        // จัดรูปแบบข้อมูลสำหรับ Highcharts
        $categories = [];
        $quantities = [];
        $sales = [];

        foreach ($data as $item) {
            $categories[] = $item['name'];
            $quantities[] = intval($item['total_qty']);
            $sales[] = floatval($item['total_sales']);
        }

        return [
            'categories' => $categories,
            'quantities' => $quantities,
            'sales' => $sales,
            'rawData' => $data
        ];
    }

    /**
     * Export ข้อมูลเป็น Excel (optional)
     */
    public function actionExport()
    {
        $fromDate = Yii::$app->request->get('from_date', date('Y-m-d', strtotime('-30 days')));
        $toDate = Yii::$app->request->get('to_date', date('Y-m-d'));

        $fromTimestamp = strtotime($fromDate);
        $toTimestamp = strtotime($toDate . ' 23:59:59');

        $salesData = $this->getSalesByProduct($fromTimestamp, $toTimestamp);

        // สร้าง CSV
        $filename = 'sales_report_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['รหัสสินค้า', 'ชื่อสินค้า', 'จำนวนขาย', 'ยอดขาย', 'ราคาเฉลี่ย', 'ต้นทุน', 'กำไร']);

        // Data
        foreach ($salesData as $row) {
            fputcsv($output, [
                $row['code'],
                $row['name'],
                $row['total_qty'],
                number_format($row['total_sales'], 2),
                number_format($row['avg_price'], 2),
                number_format($row['cost_price'], 2),
                number_format($row['profit'], 2)
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Streams an uploaded file inline for browser preview without triggering IDM download manager
     * @param string $folder
     * @param string $file
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    /**
     * Views an uploaded file inline for browser preview & printing without triggering IDM download manager
     * @param string $folder
     * @param string $file
     * @param int $raw
     * @param int $download
     * @return \yii\web\Response|string
     * @throws NotFoundHttpException
     */
    public function actionViewFile($folder = '', $file = '', $raw = 0, $download = 0)
    {
        $folder = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $folder);
        $file = basename($file);

        if (empty($file)) {
            throw new \yii\web\NotFoundHttpException('ไม่ระบุชื่อไฟล์');
        }

        $baseUploadDir = Yii::getAlias('@backend/web/uploads/');
        $fullPath = realpath($baseUploadDir . ($folder ? $folder . '/' : '') . $file);

        if (!$fullPath || !file_exists($fullPath) || strpos($fullPath, realpath($baseUploadDir)) !== 0) {
            throw new \yii\web\NotFoundHttpException('ไม่พบไฟล์เอกสาร: ' . \yii\helpers\Html::encode($file));
        }

        $mimeType = \yii\helpers\FileHelper::getMimeTypeByExtension($fullPath) ?: 'application/octet-stream';
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // If force download requested
        if ($download == 1) {
            return Yii::$app->response->sendFile($fullPath, $file, [
                'inline' => false,
                'mimeType' => $mimeType,
            ]);
        }

        // If raw binary content requested (e.g. inside iframe or img src)
        if ($raw == 1) {
            $response = Yii::$app->response;
            $response->headers->set('Content-Type', $mimeType);
            $response->headers->set('Content-Disposition', 'inline; filename="' . rawurlencode($file) . '"');
            $response->headers->set('Cache-Control', 'public, max-age=86400');

            return $response->sendFile($fullPath, $file, [
                'inline' => true,
                'mimeType' => $mimeType,
            ]);
        }

        // Render HTML Preview Wrapper (prevents IDM intercept, enables print button & new tab preview)
        $this->layout = false;
        
        $rawUrl = \yii\helpers\Url::to(['site/view-file', 'folder' => $folder, 'file' => $file, 'raw' => 1]);
        $downloadUrl = \yii\helpers\Url::to(['site/view-file', 'folder' => $folder, 'file' => $file, 'download' => 1]);
        $encodedFile = \yii\helpers\Html::encode($file);
        $encodedFolder = \yii\helpers\Html::encode($folder);

        $isPdf = ($ext === 'pdf' || $mimeType === 'application/pdf');
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พรีวิวเอกสาร - <?= $encodedFile ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #323639; color: #fff; overflow: hidden; }
        
        .toolbar {
            height: 50px;
            background: #1e222d;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            position: relative;
            z-index: 10;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 50%;
        }

        .badge-folder {
            background: #3b82f6;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-group-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-print {
            background: #10b981;
            color: #fff;
        }
        .btn-print:hover { background: #059669; }

        .btn-download {
            background: #374151;
            color: #e5e7eb;
            border: 1px solid #4b5563;
        }
        .btn-download:hover { background: #4b5563; color: #fff; }

        .btn-close-tab {
            background: #ef4444;
            color: #fff;
        }
        .btn-close-tab:hover { background: #dc2626; }

        .content-area {
            height: calc(100vh - 50px);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #525659;
        }

        .img-container {
            width: 100%;
            height: 100%;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #323639;
        }

        .img-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            background: #fff;
            border-radius: 4px;
        }

        .office-notice {
            text-align: center;
            background: #1f2937;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #374151;
            max-width: 500px;
        }

        .office-notice i {
            font-size: 64px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .office-notice h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .office-notice p {
            color: #9ca3af;
            font-size: 14px;
            margin-bottom: 20px;
        }

        @media print {
            .no-print { display: none !important; }
            body, html { height: auto !important; background: #fff !important; color: #000 !important; overflow: visible !important; }
            .content-area { height: auto !important; }
            iframe { height: 100vh !important; }
            .img-container { padding: 0 !important; background: #fff !important; }
            .img-preview { max-width: 100% !important; max-height: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>

    <div class="toolbar no-print">
        <div class="file-info">
            <i class="<?= $isPdf ? 'far fa-file-pdf text-danger' : ($isImage ? 'far fa-file-image text-info' : 'far fa-file-alt text-warning') ?>" style="font-size: 20px;"></i>
            <span><?= $encodedFile ?></span>
            <?php if (!empty($folder)): ?>
                <span class="badge-folder"><?= $encodedFolder ?></span>
            <?php endif; ?>
        </div>
        <div class="btn-group-actions">
            <button type="button" onclick="triggerPrint()" class="btn-action btn-print" title="พิมพ์เอกสาร">
                <i class="fas fa-print"></i> พิมพ์เอกสาร (Print)
            </button>
            <a href="<?= $downloadUrl ?>" class="btn-action btn-download" title="ดาวน์โหลดไฟล์">
                <i class="fas fa-download"></i> ดาวน์โหลด
            </a>
            <button type="button" onclick="window.close()" class="btn-action btn-close-tab" title="ปิดหน้าต่างนี้">
                <i class="fas fa-times"></i> ปิด
            </button>
        </div>
    </div>

    <div class="content-area">
        <?php if ($isPdf): ?>
            <iframe id="pdfFrame" src="<?= $rawUrl ?>"></iframe>
        <?php elseif ($isImage): ?>
            <div class="img-container">
                <img id="previewImg" class="img-preview" src="<?= $rawUrl ?>" alt="<?= $encodedFile ?>" />
            </div>
        <?php else: ?>
            <div class="office-notice">
                <i class="far fa-file-word"></i>
                <h3><?= $encodedFile ?></h3>
                <p>ไฟล์ประเภท <strong><?= strtoupper($ext) ?></strong> ไม่สามารถพรีวิวในเบราว์เซอร์ได้โดยตรง กรุณากดปุ่มดาวน์โหลดเพื่อเปิดด้วยโปรแกรมบนเครื่อง</p>
                <a href="<?= $downloadUrl ?>" class="btn-action btn-print" style="justify-content: center;">
                    <i class="fas fa-download"></i> ดาวน์โหลดไฟล์
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function triggerPrint() {
            var frame = document.getElementById('pdfFrame');
            if (frame) {
                try {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                    return;
                } catch (e) {
                    console.log('Frame print fallback:', e);
                }
            }
            window.print();
        }
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }

}
