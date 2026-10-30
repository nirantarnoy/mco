<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use Google\Client;
use Google\Service\Drive;

/**
 * BackupController handles automatic database backup and Google Drive upload.
 */
class BackupController extends Controller
{
    /**
     * @var string The folder ID in Google Drive where backups will be uploaded.
     * To get this, open the folder in Google Drive and copy the ID from the URL.
     * Example URL: https://drive.google.com/drive/folders/1aBcDeFgHiJkLmNoPqRsTuVwXyZ
     * Then folder ID is "1aBcDeFgHiJkLmNoPqRsTuVwXyZ".
     * If left empty, it will upload to the root directory of the Service Account.
     */
    public $googleDriveFolderId = '1Ut8MWJOa4gxPDxzKxu9ciRmubB0mwMtk';

    /**
     * ค้นหา path ของ mysqldump หรือ mysql binary
     */
    protected function getBinaryPath($binary = 'mysqldump')
    {
        $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        if ($isWin) {
            $binaryExe = $binary . '.exe';
            $paths = [
                'E:/xampp/mysql/bin/' . $binaryExe,
                'e:/xampp/mysql/bin/' . $binaryExe,
                'C:/xampp/mysql/bin/' . $binaryExe,
                'c:/xampp/mysql/bin/' . $binaryExe,
                'D:/xampp/mysql/bin/' . $binaryExe,
                'd:/xampp/mysql/bin/' . $binaryExe,
            ];
            foreach ($paths as $p) {
                if (file_exists($p)) {
                    return '"' . str_replace('/', DIRECTORY_SEPARATOR, $p) . '"';
                }
            }
        }
        return $binary;
    }

    /**
     * Executes the database backup and uploads to Google Drive.
     * Command: 
     *   php yii backup/run              (สำรองข้อมูล + บีบอัด zip + ส่ง Google Drive อัตโนมัติ)
     *   php yii backup/run --upload=0   (สำรองข้อมูลเฉพาะในเครื่องเท่านั้น ไม่ส่ง Google Drive)
     */
     public $upload = 1;

     public function options($actionID)
     {
         return array_merge(parent::options($actionID), ['upload']);
     }

    /**
     * จัดรูปแบบแสดงผลเวลา เป็น นาที และ วินาที
     */
    protected function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return round($seconds, 2) . " วินาที";
        }
        $mins = floor($seconds / 60);
        $remSecs = round(fmod($seconds, 60), 1);
        return "{$mins} นาที {$remSecs} วินาที (รวม " . round($seconds, 2) . " วิ)";
    }

    public function actionRun()
    {
        $this->stdout("===================================================\n", Console::FG_CYAN);
        $this->stdout("  MCO Database Backup (High-Speed Engine)          \n", Console::FG_CYAN, Console::BOLD);
        $this->stdout("===================================================\n", Console::FG_CYAN);

        // Get DB config
        $dsn = Yii::$app->db->dsn;
        $username = Yii::$app->db->username;
        $password = Yii::$app->db->password;

        // Parse DSN to get host and dbname
        preg_match('/host=([^;]+)/', $dsn, $hostMatches);
        preg_match('/dbname=([^;]+)/', $dsn, $dbMatches);
        
        $host = $hostMatches[1] ?? 'localhost';
        $dbname = $dbMatches[1] ?? 'mco_db';

        // Prepare backup file path in uploads/backup so it shows in Web UI as well
        $backupDir = dirname(__DIR__, 2) . '/backend/web/uploads/backup';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $dateString = date('Y-m-d_His');
        $fileName = "backup_{$dbname}_{$dateString}.sql";
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        $tempPath = $filePath . '.tmp';

        $mysqldumpPath = $this->getBinaryPath('mysqldump');
        // เพิ่ม flag เพิ่มความเร็วสูงและความปลอดภัย:
        // --single-transaction: ไม่ล็อกตาราง ป้องกันข้อมูลผิดเพี้ยน
        // --quick, --extended-insert: ลด RAM ทำงานเร็วขึ้น
        // --max_allowed_packet: ป้องกัน packet ขาดตอน
        $cmd = "{$mysqldumpPath} --default-character-set=utf8mb4 --single-transaction --quick --extended-insert --max_allowed_packet=512M --net_buffer_length=16384 -h {$host} -u {$username}";
        if (!empty($password)) {
            $cmd .= " -p" . escapeshellarg($password);
        }
        $cmd .= " {$dbname} > " . escapeshellarg($tempPath) . " 2>&1";

        $this->stdout("กำลังทำการ Dump ข้อมูลด้วย High-Speed Engine...\n", Console::FG_YELLOW);
        $startTime = microtime(true);
        exec($cmd, $output, $returnVar);

        // ตรวจสอบความสมบูรณ์: ตรวจสอบท้ายไฟล์ว่าจบด้วย Dump completed
        $isComplete = false;
        if ($returnVar === 0 && file_exists($tempPath) && filesize($tempPath) > 1024) {
            $tail = '';
            $fp = fopen($tempPath, 'rb');
            if ($fp) {
                fseek($fp, -200, SEEK_END);
                $tail = fread($fp, 200);
                fclose($fp);
            }
            if (strpos($tail, 'Dump completed') !== false) {
                $isComplete = true;
            }
        }

        if (!$isComplete) {
            @unlink($tempPath);
            $this->stderr("Error: การสำรองข้อมูลไม่สมบูรณ์และถูกยกเลิก (mysqldump ล้มเหลว)!\n", Console::FG_RED);
            if (!empty($output)) {
                $this->stderr("รายละเอียดข้อผิดพลาด: " . implode("\n", array_slice($output, 0, 5)) . "\n", Console::FG_RED);
            }
            return Controller::EXIT_CODE_ERROR;
        }

        // Rename ไฟล์ชั่วคราวเป็นไฟล์จริงหลังจากผ่านการยืนยัน 100%
        @rename($tempPath, $filePath);

        $duration = microtime(true) - $startTime;
        $timeText = $this->formatDuration($duration);
        $fileSizeMB = round(filesize($filePath) / (1024 * 1024), 2);
        $this->stdout("สำรองข้อมูลสำเร็จ 100%! ไฟล์: {$fileName} ({$fileSizeMB} MB)\n", Console::FG_GREEN);
        $this->stdout("ระยะเวลาที่ใช้ในการ Backup: {$timeText}\n", Console::FG_GREEN, Console::BOLD);

        // Upload to Google Drive เฉพาะเมื่อเปิด option --upload=1
        if ($this->upload) {
            $this->stdout("กำลังดำเนินการอัปโหลดขึ้น Google Drive...\n", Console::FG_YELLOW);
            $clientSecretPath = Yii::getAlias('@console/config/client_secret.json');
            if (file_exists($clientSecretPath)) {
                $zipFilePath = $filePath . '.zip';
                $zip = new \ZipArchive();
                if ($zip->open($zipFilePath, \ZipArchive::CREATE) === TRUE) {
                    $zip->addFile($filePath, $fileName);
                    $zip->close();
                    if ($this->uploadToGoogleDrive($zipFilePath, $fileName . '.zip')) {
                        $this->stdout("อัปโหลดไฟล์ขึ้น Google Drive สำเร็จ!\n", Console::FG_GREEN);
                    }
                    @unlink($zipFilePath);
                }
            }
        }

        $this->stdout("===================================================\n", Console::FG_CYAN);
        $this->stdout("กระบวนการ Backup เสร็จสมบูรณ์ 100%\n", Console::FG_GREEN, Console::BOLD);
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * กู้คืนฐานข้อมูลผ่าน Console ด้วยความเร็วสูง พร้อมแสดง Progress
     * คำสั่ง:
     *   php yii backup/restore
     * หรือ
     *   php yii backup/restore <ชื่อไฟล์.sql หรือ path เต็ม>
     */
    public function actionRestore($file = null)
    {
        $this->stdout("===================================================\n", Console::FG_CYAN);
        $this->stdout("  MCO Database Restore Console Engine (High-Speed) \n", Console::FG_CYAN, Console::BOLD);
        $this->stdout("===================================================\n\n", Console::FG_CYAN);

        $backupDir = dirname(__DIR__, 2) . '/backend/web/uploads/backup';

        if (empty($file)) {
            // ดึงไฟล์ล่าสุดใน uploads/backup
            $files = glob($backupDir . '/*.sql');
            if (empty($files)) {
                $this->stderr("ไม่พบไฟล์ .sql ในโฟลเดอร์: {$backupDir}\n", Console::FG_RED);
                $this->stdout("กรุณาระบุไฟล์ เช่น: php yii backup/restore my_backup.sql\n", Console::FG_YELLOW);
                return Controller::EXIT_CODE_ERROR;
            }
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $filePath = $files[0];
            $fileName = basename($filePath);
            $this->stdout("พบไฟล์สำรองล่าสุด: {$fileName}\n", Console::FG_CYAN);
        } else {
            if (file_exists($file)) {
                $filePath = $file;
                $fileName = basename($file);
            } elseif (file_exists($backupDir . DIRECTORY_SEPARATOR . $file)) {
                $filePath = $backupDir . DIRECTORY_SEPARATOR . $file;
                $fileName = $file;
            } else {
                $this->stderr("ไม่พบไฟล์ที่ระบุ: {$file}\n", Console::FG_RED);
                return Controller::EXIT_CODE_ERROR;
            }
        }

        $fileSize = filesize($filePath);
        $fileSizeMB = round($fileSize / (1024 * 1024), 2);

        $this->stdout("ไฟล์ที่เลือก: {$fileName} (ขนาด: {$fileSizeMB} MB)\n", Console::FG_YELLOW);

        // Get DB config
        $dsn = Yii::$app->db->dsn;
        $username = Yii::$app->db->username;
        $password = Yii::$app->db->password;

        preg_match('/host=([^;]+)/', $dsn, $hostMatches);
        preg_match('/dbname=([^;]+)/', $dsn, $dbMatches);
        $host = $hostMatches[1] ?? 'localhost';
        $dbname = $dbMatches[1] ?? 'mco_db';

        $this->stdout("ฐานข้อมูลเป้าหมาย: {$dbname} @ {$host}\n", Console::FG_YELLOW);
        $this->stdout("คำเตือน: การ Restore จะเขียนทับข้อมูลในฐานข้อมูล '{$dbname}' ทั้งหมด!\n", Console::FG_RED, Console::BOLD);

        if (!$this->confirm("คุณต้องการดำเนินการต่อหรือไม่?")) {
            $this->stdout("ยกเลิกการทำงาน.\n", Console::FG_GREY);
            return Controller::EXIT_CODE_NORMAL;
        }

        $mysqlPath = $this->getBinaryPath('mysql');

        $this->stdout("\nเริ่มต้นนำเข้าข้อมูลผ่าน High-Speed MySQL CLI Engine...\n", Console::FG_GREEN);
        $startTime = microtime(true);

        // รันแบบ Chunk Streaming พร้อมแสดง Progress
        $cmd = "{$mysqlPath} --default-character-set=utf8mb4 --max_allowed_packet=512M -h {$host} -u {$username}";
        if (!empty($password)) {
            $cmd .= " -p" . escapeshellarg($password);
        }
        $cmd .= " {$dbname}";

        $descriptors = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            $this->stderr("ไม่สามารถเปิดโพรเซส MySQL CLI ได้!\n", Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        $sourceFile = fopen($filePath, 'rb');
        $bytesProcessed = 0;
        $chunkSize = 1024 * 1024 * 2; // อ่านทีละ 2MB เพื่อความรวดเร็วและ memory ต่ำ

        $lastPercent = -1;
        $isFirstChunk = true;
        while (!feof($sourceFile)) {
            $buffer = fread($sourceFile, $chunkSize);
            
            // กรอง comment MariaDB sandbox mode ที่ทำให้เกิด error '\-'
            if ($isFirstChunk) {
                if (strpos($buffer, '/*M!999999\\-') !== false) {
                    $buffer = preg_replace('/\/\*M!999999\\\\-[^*]*\*\//', '-- MariaDB sandbox mode', $buffer);
                }
                $isFirstChunk = false;
            }

            fwrite($pipes[0], $buffer);
            $bytesProcessed += strlen($buffer);

            $percent = $fileSize > 0 ? min(100, round(($bytesProcessed / $fileSize) * 100)) : 100;
            if ($percent !== $lastPercent) {
                $lastPercent = $percent;
                $mbDone = round($bytesProcessed / (1024 * 1024), 1);
                
                // วาด progress bar
                $barLength = 30;
                $filled = floor(($percent / 100) * $barLength);
                $empty = $barLength - $filled;
                $bar = str_repeat("=", $filled) . ($percent < 100 ? ">" : "=") . str_repeat(" ", max(0, $empty - 1));

                $this->stdout("\rProgress: [{$bar}] {$percent}% ({$mbDone}/{$fileSizeMB} MB)", Console::FG_GREEN);
            }
        }

        fclose($sourceFile);
        fclose($pipes[0]);

        $stderrOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        $this->stdout("\n");

        if ($returnCode === 0) {
            $duration = microtime(true) - $startTime;
            $timeText = $this->formatDuration($duration);
            $this->stdout("\n===================================================\n", Console::FG_GREEN);
            $this->stdout("  RESTORE ข้อมูลสำเร็จ 100% เรียบร้อยแล้ว!  \n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  ขนาด: {$fileSizeMB} MB | ระยะเวลาที่ใช้: {$timeText}\n", Console::FG_GREEN);
            $this->stdout("===================================================\n", Console::FG_GREEN);
            return Controller::EXIT_CODE_NORMAL;
        } else {
            $duration = microtime(true) - $startTime;
            $timeText = $this->formatDuration($duration);
            $this->stderr("เกิดข้อผิดพลาดในการ Restore (Exit code: {$returnCode}, ใช้เวลา: {$timeText})\n", Console::FG_RED);
            if (!empty($stderrOutput)) {
                $this->stderr("รายละเอียดข้อผิดพลาด: {$stderrOutput}\n", Console::FG_RED);
            }
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Uploads a file to Google Drive using OAuth 2.0 (User Consent).
     */
    protected function uploadToGoogleDrive($filePath, $fileName)
    {
        $clientSecretPath = Yii::getAlias('@console/config/client_secret.json');
        $tokenPath = Yii::getAlias('@console/config/token.json');
        
        if (!file_exists($clientSecretPath)) {
            $this->stderr("OAuth Client Secret not found at: {$clientSecretPath}\n", Console::FG_RED);
            $this->stderr("Please download it from Google Cloud Console.\n", Console::FG_RED);
            return false;
        }

        try {
            $client = new Client();
            $client->setAuthConfig($clientSecretPath);
            $client->addScope(Drive::DRIVE_FILE);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);
            } else {
                $this->stderr("Token file not found at: {$tokenPath}\n", Console::FG_RED);
                $this->stderr("Please run 'php console/config/get-token.php' to generate it.\n", Console::FG_RED);
                return false;
            }

            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                } else {
                    $this->stderr("Refresh token missing. Please delete token.json and re-run get-token.php\n", Console::FG_RED);
                    return false;
                }
            }
            
            $driveService = new Drive($client);

            $fileMetadata = new Drive\DriveFile([
                'name' => $fileName
            ]);
            
            if (!empty($this->googleDriveFolderId)) {
                $fileMetadata->setParents([$this->googleDriveFolderId]);
            }

            $mimeType = function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/zip';
            $content = file_get_contents($filePath);

            $file = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id',
                'supportsAllDrives' => true
            ]);

            $this->stdout("File ID: " . $file->id . "\n", Console::FG_GREEN);
            return true;

        } catch (\Exception $e) {
            $this->stderr("Google Drive Upload Error: " . $e->getMessage() . "\n", Console::FG_RED);
            return false;
        }
    }
}
