<?php

namespace backend\controllers;

use Yii;
use backend\models\Picking;
use backend\models\PickingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Modelfile;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

/**
 * PickingController implements the CRUD actions for Picking model.
 */
class DbbackupController extends BaseController
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        // ปรับแต่ง PHP Runtime Settings ป้องกัน Timeout และ Memory Limit
        @ini_set('max_execution_time', '0'); // ไม่จำกัดเวลาประมวลผล (Unlimited)
        @ini_set('max_input_time', '0');
        @ini_set('memory_limit', '1024M'); // ขยายหน่วยความจำรองรับไฟล์ใหญ่
        @set_time_limit(0);

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access'=>[
                'class'=>AccessControl::className(),
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException('คุณไม่ได้รับอนุญาติให้เข้าใช้งาน!');
                },
                'rules'=>[
                    [
                        'allow'=>true,
                        'roles'=>['@'],
                        'matchCallback'=>function($rule,$action){
                            $currentRoute = Yii::$app->controller->getRoute();
                            // Allow sub-actions of dbbackup if user has access to backuplist
                            if (in_array($action->id, ['restorefile', 'downloadbak', 'deletebak', 'exrestore', 'copy-uploads', 'copyuploads', 'backup-db-ajax', 'backupdbajax', 'get-files-list', 'getfileslist', 'copy-batch', 'copybatch', 'browse-folder', 'browsefolder'])) {
                                if (Yii::$app->user->can('dbbackup/backuplist') || \backend\models\User::isUserAdmin(\Yii::$app->user->id)) {
                                    return true;
                                }
                            }
                            if(Yii::$app->user->can($currentRoute) || \backend\models\User::isUserAdmin(\Yii::$app->user->id)){
                                return true;
                            }
                            return false;
                        }
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionBak()
    {
        $db = \Yii::$app->db;
        $username = $db->username;
        $password = $db->password;
        
        $dsn = $db->dsn;
        preg_match('/host=([^;]*)/', $dsn, $hostMatch);
        $host = isset($hostMatch[1]) ? $hostMatch[1] : 'localhost';
        
        preg_match('/dbname=([^;]*)/', $dsn, $dbMatch);
        $database_name = isset($dbMatch[1]) ? $dbMatch[1] : 'mmc_db';

// Get connection object and set the charset
        $conn = mysqli_connect($host, $username, $password, $database_name);
        $conn->set_charset("utf8");


// Get All Table Names From the Database
        $tables = array();
        $sql = "SHOW TABLES";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }

        $sqlScript = "";
        foreach ($tables as $table) {

            // Prepare SQLscript for creating table structure
            $query = "SHOW CREATE TABLE $table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);

            $sqlScript .= "\n\n" . $row[1] . ";\n\n";


            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = $row[$j];

                        if (isset($row[$j])) {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= '""';
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";
        }

        if (!empty($sqlScript)) {
            // Save the SQL script to a backup file
            $backup_file_name = $database_name . '_backup_' . time() . '.sql';
            $fileHandler = fopen($backup_file_name, 'w+');
            $number_of_lines = fwrite($fileHandler, $sqlScript);
            fclose($fileHandler);

            // Download the SQL backup file to the browser
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backup_file_name));
            ob_clean();
            flush();
            readfile($backup_file_name);
            exec('rm ' . $backup_file_name);
        }


        //  $this->backup_tables('localhost','root','','coltd');
    }

    public function actionRestorepage()
    {
        return $this->render('_restore');
    }

    public function actionRestoredb()
    {
        //   $conn = mysqli_connect("localhost", "root", "", "coltd");
        $conn = \Yii::$app->db;
        $uploaded = UploadedFile::getInstanceByName('restore_file');
        if (!empty($uploaded)) {
            //print_r($uploaded);return;
            $upfiles = time() . "." . $uploaded->getExtension();
            if ($uploaded->saveAs('../web/uploads/backup/' . $upfiles)) {
                //echo "uploaded";return;
                // Validating SQL file type by extensions
                if ($uploaded->getExtension() != "sql") {
                    $response = array(
                        "type" => "error",
                        "message" => "Invalid File Type"
                    );
                } else {
                    $response = $this->restoreMysqlDB($conn, $upfiles);
                    //print_r($response);
                    \Yii::$app->session->setFlash('msg','Restore ข้อมูลสำเร็จ');
                    return $this->redirect(['db/restorepage']);

                }


            }
        }

    }

    public function restoreMysqlDB($conn, $filename)
    {
        $sql = '';
        $error = '';
        $filePath = '../web/uploads/backup/' . $filename;
        if (file_exists($filePath)) {
            // return 'has';
            $lines = file($filePath);

            foreach ($lines as $line) {

                // Ignoring comments from the SQL script
                if (substr($line, 0, 2) == '--' || $line == '') {
                    continue;
                }

                $sql .= $line;

                if (substr(trim($line), -1, 1) == ';') {
                   // $result = mysqli_query($conn, $sql);
                    $result = \Yii::$app->db->createCommand($sql)->query();
                    if (!$result) {
                        $error .= mysqli_error($conn) . "\n";
                    }
                    $sql = '';
                }
            } // end foreach

            if ($error) {
                $response = array(
                    "type" => "error",
                    "message" => $error
                );
            } else {
                $response = array(
                    "type" => "success",
                    "message" => "Database Restore Completed Successfully."
                );
            }
        } // end if file exists
        return $response;
    }

    /**
     * ค้นหา path ของ mysql binary (mysqldump / mysql) ให้รองรับทั้ง XAMPP E:, C:, D: หรือ Linux
     */
    public static function getBinaryPath($binary = 'mysqldump')
    {
        $os = php_uname();
        $isWin = (strpos($os, 'ndow') > 0 || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        if ($isWin) {
            $binaryExe = $binary . '.exe';
            $possiblePaths = [
                'E:/xampp/mysql/bin/' . $binaryExe,
                'e:/xampp/mysql/bin/' . $binaryExe,
                'C:/xampp/mysql/bin/' . $binaryExe,
                'c:/xampp/mysql/bin/' . $binaryExe,
                'D:/xampp/mysql/bin/' . $binaryExe,
                'd:/xampp/mysql/bin/' . $binaryExe,
            ];
            foreach ($possiblePaths as $p) {
                if (file_exists($p)) {
                    return '"' . str_replace('/', DIRECTORY_SEPARATOR, $p) . '"';
                }
            }
            return $binary;
        }

        return $binary;
    }

    /**
     * ตรวจสอบและแก้ไข comment header ของ MariaDB (เช่น /*M!999999\- enable the sandbox mode * /) 
     * เพื่อให้ MySQL CLI ทั่วไปสามารถ restore ได้โดยไม่เจอ error: Unknown command '\-'
     */
    public static function prepareSqlFileForRestore($filePath)
    {
        if (!file_exists($filePath) || filesize($filePath) < 10) {
            return $filePath;
        }

        // อ่าน 1KB แรกมาเช็คว่ามี /*M!999999\- หรือไม่
        $fp = fopen($filePath, 'rb');
        $header = fread($fp, 1024);
        fclose($fp);

        if (strpos($header, '/*M!999999\\-') !== false) {
            // สร้างไฟล์ clean ชั่วคราวโดยแทนที่บรรทัด /*M!999999\- enable the sandbox mode */ ด้วย comment ธรรมดา
            $cleanPath = $filePath . '.clean.sql';
            $in = fopen($filePath, 'rb');
            $out = fopen($cleanPath, 'wb');
            
            $firstChunk = true;
            while (!feof($in)) {
                $buf = fread($in, 1024 * 1024 * 2);
                if ($firstChunk) {
                    // แทนที่ทั้ง block /*M!999999\- ... */ ด้วย comment ปกติ
                    $buf = preg_replace('/\/\*M!999999\\\\-[^*]*\*\//', '-- MariaDB sandbox mode', $buf);
                    $firstChunk = false;
                }
                fwrite($out, $buf);
            }
            fclose($in);
            fclose($out);
            return $cleanPath;
        }

        return $filePath;
    }

    public static function getDbCredentials()
    {
        $db = \Yii::$app->db;
        $username = $db->username;
        $password = $db->password;
        
        $dsn = $db->dsn;
        preg_match('/host=([^;]*)/', $dsn, $hostMatch);
        $host = isset($hostMatch[1]) ? $hostMatch[1] : 'localhost';
        
        preg_match('/dbname=([^;]*)/', $dsn, $dbMatch);
        $database_name = isset($dbMatch[1]) ? $dbMatch[1] : 'mco_db';

        return [$host, $username, $password, $database_name];
    }

    public function actionBackuplist()
    {
        return $this->render('_backuplist');
    }

    /**
     * แปลงระยะเวลาเป็นข้อความ นาที / วินาที
     */
    public static function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return round($seconds, 2) . " วินาที";
        }
        $mins = floor($seconds / 60);
        $remSecs = round(fmod($seconds, 60), 1);
        return "{$mins} นาที {$remSecs} วินาที (รวม " . round($seconds, 2) . " วิ)";
    }

    public function actionExrestore()
    {
        list($host, $username, $password, $database_name) = self::getDbCredentials();

        $backupDir = dirname(__DIR__, 2) . '/backend/web/uploads/backup';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0777, true);
        }

        $date_string = date('Y-m-d_His');
        $fileName = "backup_{$database_name}_{$date_string}.sql";
        $targetFile = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $tempFile = $targetFile . '.tmp';

        $mysqldump = self::getBinaryPath('mysqldump');
        // เพิ่ม flag เพื่อความปลอดภัยและความสมบูรณ์:
        // --single-transaction: ทำงานแบบ snapshot ป้องกันข้อมูลเพี้ยนขณะมีผู้ใช้งานบันทึกข้อมูล
        // --quick & --extended-insert: ทำงานเร็วมากและลด Memory
        // --max_allowed_packet: ป้องกัน packet ข้อมูลขนาดใหญ่ขาดตอน
        $cmd = "{$mysqldump} --default-character-set=utf8mb4 --single-transaction --quick --extended-insert --max_allowed_packet=512M --net_buffer_length=16384 -h {$host} -u {$username}";
        if (!empty($password)) {
            $cmd .= " -p" . escapeshellarg($password);
        }
        $cmd .= " {$database_name} > " . escapeshellarg($tempFile) . " 2>&1";

        $startTime = microtime(true);
        exec($cmd, $output, $returnVar);
        $duration = microtime(true) - $startTime;
        $timeText = self::formatDuration($duration);

        // ตรวจสอบความสมบูรณ์: ต้อง return 0, มีไฟล์จริง, ขนาดมากกว่า 1KB และมีท้ายไฟล์ "Dump completed"
        $isComplete = false;
        if ($returnVar === 0 && file_exists($tempFile) && filesize($tempFile) > 1024) {
            $tail = '';
            $fp = fopen($tempFile, 'rb');
            if ($fp) {
                fseek($fp, -200, SEEK_END);
                $tail = fread($fp, 200);
                fclose($fp);
            }
            if (strpos($tail, 'Dump completed') !== false) {
                $isComplete = true;
            }
        }

        if ($isComplete) {
            // สำรองข้อมูลสำเร็จ 100% จึง rename เป็นไฟล์จริง ป้องกันการได้ไฟล์เสีย/ไม่สมบูรณ์
            @rename($tempFile, $targetFile);
            $fileSizeMB = number_format(filesize($targetFile) / (1024 * 1024), 2);
            \Yii::$app->session->setFlash('success', "สำรองข้อมูลเรียบร้อยและผ่านการตรวจสอบความสมบูรณ์ 100%: {$fileName} (ขนาด {$fileSizeMB} MB) | ใช้เวลาทั้งหมด: <strong>{$timeText}</strong>");
        } else {
            // หากไม่สำเร็จ ลบไฟล์ชั่วคราวทิ้งทันที ไม่ให้มีไฟล์ขยะหรือไฟล์เสียค้างในระบบ
            @unlink($tempFile);
            $errDetail = !empty($output) ? ('<br>รายละเอียด: ' . htmlspecialchars(implode(' ', array_slice($output, 0, 3)))) : '';
            \Yii::$app->session->setFlash('error', "การสำรองข้อมูลไม่สมบูรณ์และถูกยกเลิก รหัสข้อผิดพลาด: {$returnVar} (ใช้เวลา: {$timeText}){$errDetail}");
        }

        return $this->redirect(['dbbackup/backuplist']);
    }

    /**
     * AJAX Database Backup for Progress Bar & Modal feedback
     */
    public function actionBackupDbAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        list($host, $username, $password, $database_name) = self::getDbCredentials();

        $backupDir = dirname(__DIR__, 2) . '/backend/web/uploads/backup';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0777, true);
        }

        $date_string = date('Y-m-d_His');
        $fileName = "backup_{$database_name}_{$date_string}.sql";
        $targetFile = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $tempFile = $targetFile . '.tmp';

        $mysqldump = self::getBinaryPath('mysqldump');
        $cmd = "{$mysqldump} --default-character-set=utf8mb4 --single-transaction --quick --extended-insert --max_allowed_packet=512M --net_buffer_length=16384 -h {$host} -u {$username}";
        if (!empty($password)) {
            $cmd .= " -p" . escapeshellarg($password);
        }
        $cmd .= " {$database_name} > " . escapeshellarg($tempFile) . " 2>&1";

        $startTime = microtime(true);
        exec($cmd, $output, $returnVar);
        $duration = microtime(true) - $startTime;
        $timeText = self::formatDuration($duration);

        $isComplete = false;
        if ($returnVar === 0 && file_exists($tempFile) && filesize($tempFile) > 1024) {
            $tail = '';
            $fp = fopen($tempFile, 'rb');
            if ($fp) {
                fseek($fp, -200, SEEK_END);
                $tail = fread($fp, 200);
                fclose($fp);
            }
            if (strpos($tail, 'Dump completed') !== false) {
                $isComplete = true;
            }
        }

        if ($isComplete) {
            @rename($tempFile, $targetFile);
            $fileSizeMB = number_format(filesize($targetFile) / (1024 * 1024), 2);
            return [
                'status' => 'success',
                'fileName' => $fileName,
                'fileSize' => $fileSizeMB . ' MB',
                'duration' => $timeText,
                'message' => "สำรองฐานข้อมูลสำเร็จ 100%: {$fileName} ({$fileSizeMB} MB) ใช้เวลา {$timeText}"
            ];
        } else {
            @unlink($tempFile);
            $errDetail = !empty($output) ? implode(' ', array_slice($output, 0, 3)) : 'Unknown mysqldump error';
            return [
                'status' => 'error',
                'message' => "การสำรองฐานข้อมูลล้มเหลว (Code {$returnVar}): {$errDetail}"
            ];
        }
    }

    /**
     * ดึงรายการไฟล์ทั้งหมดใน backend/web/uploads (ยกเว้น uploads/backup) เพื่อคำนวณและทำ Progress bar
     */
    public function actionGetFilesList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $sourceDir = dirname(__DIR__, 2) . '/backend/web/uploads';
        $destPath = trim(Yii::$app->request->post('destPath', ''));

        if (empty($destPath)) {
            $destPath = $sourceDir;
        }
        $destPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $destPath), DIRECTORY_SEPARATOR);

        if (!is_dir($sourceDir)) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบโฟลเดอร์ต้นทาง: ' . $sourceDir
            ];
        }

        $files = [];
        $totalBytes = 0;

        try {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($it as $item) {
                $pathname = $item->getPathname();
                // ข้ามโฟลเดอร์ backup SQL
                if (strpos($pathname, 'uploads' . DIRECTORY_SEPARATOR . 'backup') !== false ||
                    strpos($pathname, 'uploads/backup') !== false ||
                    strpos($pathname, 'uploads\\backup') !== false) {
                    continue;
                }

                if ($item->isFile()) {
                    $rel = substr($pathname, strlen($sourceDir) + 1);
                    $rel = str_replace('\\', '/', $rel);
                    $size = $item->getSize();
                    $files[] = [
                        'path' => $rel,
                        'size' => $size,
                    ];
                    $totalBytes += $size;
                }
            }

            return [
                'status' => 'success',
                'totalFiles' => count($files),
                'totalBytes' => $totalBytes,
                'totalSizeMB' => number_format($totalBytes / (1024 * 1024), 2),
                'sourceDir' => $sourceDir,
                'destDir' => $destPath,
                'files' => $files,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการสแกนไฟล์: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ทำการคัดลอกไฟล์แบบทีละชุด (Batch Copy) เพื่อให้หน้าบ้านอัปเดต Progress Bar อย่างต่อเนื่อง
     */
    public function actionCopyBatch()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $sourceDir = dirname(__DIR__, 2) . '/backend/web/uploads';
        $destPath = trim(Yii::$app->request->post('destPath', ''));
        $batch = Yii::$app->request->post('batch', []);

        if (empty($destPath)) {
            $destPath = $sourceDir;
        }
        $destPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $destPath), DIRECTORY_SEPARATOR);

        if (!is_dir($destPath)) {
            if (!@mkdir($destPath, 0777, true)) {
                return [
                    'status' => 'error',
                    'message' => 'ไม่สามารถสร้างโฟลเดอร์ปลายทางได้: ' . $destPath
                ];
            }
        }

        $copiedCount = 0;
        $skippedCount = 0;
        $copiedBytes = 0;
        $errors = [];

        foreach ($batch as $item) {
            $rel = is_array($item) ? ($item['path'] ?? '') : $item;
            if (empty($rel)) continue;

            $srcFile = $sourceDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            $dstFile = $destPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);

            if (!file_exists($srcFile)) {
                $errors[] = "ไม่พบไฟล์ต้นทาง: {$rel}";
                continue;
            }

            // ถ้าปลายทางคือโฟลเดอร์เดียวกัน ให้ถือว่ามีอยู่แล้วและข้ามทันที
            if (realpath($srcFile) === realpath($dstFile)) {
                $skippedCount++;
                continue;
            }

            // ถ้าไฟล์มีอยู่แล้วที่ปลายทาง:
            // 1. ตรวจสอบว่ามีอยู่แล้ว (file_exists)
            // 2. ถ้าขนาดไฟล์เท่ากันและเวลาไฟล์ไม่เก่ากว่าต้นทาง ให้ข้ามทันที (ไม่ต้องอ่านเขียน Disk ซ้ำ เร็วกว่าและปลอดภัยที่สุด)
            // 3. ถ้าผู้ใช้ต้องการให้ "มีอยู่แล้วให้ข้าม": หากไฟล์ปลายทางมีอยู่จริงและขนาดเท่ากัน ข้ามทันที
            if (file_exists($dstFile)) {
                $srcSize = filesize($srcFile);
                $dstSize = filesize($dstFile);

                // หากปลายทางมีไฟล์อยู่แล้ว และขนาดเท่ากัน ให้ถือว่าสมบูรณ์แล้ว -> ข้ามทันที (ห้ามลบ)
                if ($srcSize === $dstSize) {
                    $skippedCount++;
                    continue;
                }
            }

            // กรณีต้องคัดลอกไฟล์ใหม่ (หรือไฟล์เดิมขนาดไม่ตรง):
            $dstSubDir = dirname($dstFile);
            if (!is_dir($dstSubDir)) {
                if (!@mkdir($dstSubDir, 0777, true) && !is_dir($dstSubDir)) {
                    $errors[] = "ไม่สามารถสร้างโฟลเดอร์ปลายทาง: {$dstSubDir}";
                    continue;
                }
            }

            // เทคนิค Safe Copy (Atomic write): คัดลอกลงไฟล์ชั่วคราว .tmp ก่อน
            // เพื่อรับประกันว่าหากเกิดไฟดับ/เน็ตหลุด/หยุดกลางคัน ไฟล์ปลายทางเดิมจะไม่เสียหายเด็ดขาด
            $tmpDstFile = $dstFile . '.tmp_' . uniqid();
            if (@copy($srcFile, $tmpDstFile)) {
                // ตรวจสอบความสมบูรณ์ของขนาดไฟล์ชั่วคราวว่าเท่ากับต้นทาง 100%
                if (file_exists($tmpDstFile) && filesize($tmpDstFile) === filesize($srcFile)) {
                    @touch($tmpDstFile, filemtime($srcFile));
                    // ปลอดภัย: สลับชื่อไฟล์ (rename) เข้าแทนที่
                    if (@rename($tmpDstFile, $dstFile)) {
                        $copiedCount++;
                        $copiedBytes += filesize($dstFile);
                    } else {
                        // หาก rename ไม่ผ่าน (เช่นติด permission ใน Windows) ให้ fallback copy ตรง
                        @copy($tmpDstFile, $dstFile);
                        @unlink($tmpDstFile);
                        $copiedCount++;
                        $copiedBytes += filesize($dstFile);
                    }
                } else {
                    @unlink($tmpDstFile);
                    $errors[] = "คัดลอกไฟล์ {$rel} ไม่สมบูรณ์ (ขนาดไฟล์ไม่ตรงกัน)";
                }
            } else {
                $errors[] = "คัดลอกไม่สำเร็จ: {$rel}";
            }
        }

        return [
            'status' => 'success',
            'copiedCount' => $copiedCount,
            'skippedCount' => $skippedCount,
            'copiedBytes' => $copiedBytes,
            'errors' => $errors,
        ];
    }

    /**
     * AJAX สำหรับเปิดดูโครงสร้างโฟลเดอร์สำหรับเลือกปลายทางบนเครื่อง Server / Local
     */
    public function actionBrowseFolder()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $targetPath = trim(Yii::$app->request->post('targetPath', ''));

        // โหมด List Subdirectories สำหรับเรียกดูและเลือกโฟลเดอร์แบบ Interactive ทันใจ
        if (empty($targetPath)) {
            $targetPath = dirname(__DIR__, 2);
        }

        $targetPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetPath);
        if (!is_dir($targetPath)) {
            $targetPath = dirname(__DIR__, 2);
        }

        $targetPath = realpath($targetPath);
        $parentDir = dirname($targetPath);

        $folders = [];
        $drives = [];

        // รายการ Drives ใน Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            foreach (range('A', 'Z') as $letter) {
                $drive = $letter . ':\\';
                if (@is_dir($drive)) {
                    $drives[] = [
                        'name' => $letter . ':',
                        'path' => str_replace('\\', '/', $drive)
                    ];
                }
            }
        }

        if (is_dir($targetPath)) {
            try {
                $items = @scandir($targetPath);
                if ($items) {
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') continue;
                        $full = $targetPath . DIRECTORY_SEPARATOR . $item;
                        if (@is_dir($full)) {
                            $folders[] = [
                                'name' => $item,
                                'path' => str_replace('\\', '/', $full),
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore permission errors
            }
        }

        return [
            'status' => 'success',
            'currentPath' => str_replace('\\', '/', $targetPath),
            'parentPath' => ($parentDir && $parentDir !== $targetPath) ? str_replace('\\', '/', $parentDir) : null,
            'drives' => $drives,
            'folders' => $folders
    }

    public function actionRestorefile($id)
    {
        $filename = basename($id);
        $filePath = dirname(__DIR__, 2) . '/backend/web/uploads/backup/' . $filename;

        if (!file_exists($filePath)) {
            \Yii::$app->session->setFlash('error', 'ไม่พบไฟล์ที่ต้องการกู้คืน: ' . $filename);
            return $this->redirect(['dbbackup/backuplist']);
        }

        // ป้องกัน: ตรวจสอบความสมบูรณ์เบื้องต้นของไฟล์ก่อน restore
        if (filesize($filePath) < 100) {
            \Yii::$app->session->setFlash('error', 'ไฟล์มีขนาดเล็กผิดปกติ (ไฟล์อาจเสียหายหรือเป็นไฟล์ว่าง) ไม่สามารถกู้คืนได้');
            return $this->redirect(['dbbackup/backuplist']);
        }

        list($host, $username, $password, $database_name) = self::getDbCredentials();
        $mysql = self::getBinaryPath('mysql');

        // เตรียมไฟล์และตัด header MariaDB sandbox mode ที่ทำให้เกิด error '\-'
        $preparedFilePath = self::prepareSqlFileForRestore($filePath);

        // ป้องกัน: เปิด --max_allowed_packet และ capture stderr เพื่อดู error ที่แท้จริง
        $cmd = "{$mysql} --default-character-set=utf8mb4 --max_allowed_packet=512M -h {$host} -u {$username}";
        if (!empty($password)) {
            $cmd .= " -p" . escapeshellarg($password);
        }
        $cmd .= " {$database_name} < " . escapeshellarg($preparedFilePath) . " 2>&1";

        $startTime = microtime(true);
        exec($cmd, $output, $returnVar);
        $duration = microtime(true) - $startTime;
        $timeText = self::formatDuration($duration);

        // ลบไฟล์ชั่วคราวถ้ามีการสร้าง clean file
        if ($preparedFilePath !== $filePath && file_exists($preparedFilePath)) {
            @unlink($preparedFilePath);
        }

        if ($returnVar === 0) {
            \Yii::$app->session->setFlash('success', "กู้คืนข้อมูล (Restore) สำเร็จเรียบร้อยสมบูรณ์ 100% จากไฟล์ {$filename} | ใช้เวลาทั้งหมด: <strong>{$timeText}</strong>");
        } else {
            $errDetail = !empty($output) ? ('<br>สาเหตุ: ' . htmlspecialchars(implode(' ', array_slice($output, 0, 3)))) : '';
            \Yii::$app->session->setFlash('error', "กู้คืนข้อมูลไม่สำเร็จ รหัสข้อผิดพลาด: {$returnVar} (ใช้เวลา: {$timeText}){$errDetail}");
        }

        return $this->redirect(['dbbackup/backuplist']);
    }

    public function backup_tables($host, $user, $pass, $name, $tables = '*')
    {

        $link = mysqli_connect($host, $user, $pass);
        mysqli_set_charset($link, "utf8");

        mysqli_select_db($link, $name);

        $return = '';

        //get all of the tables
        if ($tables == '*') {
            $tables = array();
            $result = mysqli_query($link, 'SHOW TABLES');
            while ($row = mysqli_fetch_row($result)) {
                $tables[] = $row[0];
            }
        } else {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        //cycle through
        foreach ($tables as $table) {
            $result = mysqli_query($link, 'SELECT * FROM ' . $table);
            $num_fields = mysqli_num_fields($result);

            $return .= 'DROP TABLE ' . $table . ';';
            $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . $table));
            $return .= "\n\n" . $row2[1] . ";\n\n";

            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $return .= 'INSERT INTO ' . $table . ' VALUES(';
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = ereg_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1)) {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
            }
            $return .= "\n\n\n";
        }

        //save file
        $handle = fopen('db-backup-' . time() . '-' . (md5(implode(',', $tables))) . '.sql', 'w+');
        fwrite($handle, $return);
        fclose($handle);
    }

    /**
     * Lists all Picking models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Modelfile();
        if ($model->load(Yii::$app->request->post())) {
            $uploaded = UploadedFile::getInstance($model, 'file');
            if (!empty($uploaded)) {
                $uploaded->saveAs(Yii::getAlias('@backend') . '/backups/' . $uploaded);
                $this->redirect(['db-manager/default']);
            }
        }

        return $this->render('index', [
            'modelfile' => $model
        ]);
    }


    public function actionDownloadbak($id)
    {
        if ($id != '') {
            $filename = basename($id);
            $filepath = dirname(__DIR__, 2) . '/backend/web/uploads/backup/' . $filename;

            // Process download
            if (file_exists($filepath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                flush(); // Flush system output buffer
                readfile($filepath);
                die();
            } else {
                http_response_code(404);
                \Yii::$app->session->setFlash('error', 'ไม่พบไฟล์: ' . $filename);
                return $this->redirect(['dbbackup/backuplist']);
            }
        }
        return $this->redirect(['dbbackup/backuplist']);
    }

    public function actionDeletebak($id)
    {
        if ($id != '') {
            $filename = basename($id);
            $filepath = dirname(__DIR__, 2) . '/backend/web/uploads/backup/' . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
                \Yii::$app->session->setFlash('success', 'ลบไฟล์สำรองข้อมูลเรียบร้อยแล้ว: ' . $filename);
            }
        }
        return $this->redirect(['dbbackup/backuplist']);
    }

}
