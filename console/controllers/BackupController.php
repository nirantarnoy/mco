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
     * Executes the database backup and uploads to Google Drive.
     * Command: php yii backup/run
     */
    public function actionRun()
    {
        $this->stdout("Starting Database Backup...\n", Console::FG_GREEN);

        // Get DB config
        $dsn = Yii::$app->db->dsn; // e.g. mysql:host=localhost;dbname=mmc_db
        $username = Yii::$app->db->username;
        $password = Yii::$app->db->password;

        // Parse DSN to get host and dbname
        preg_match('/host=([^;]+)/', $dsn, $hostMatches);
        preg_match('/dbname=([^;]+)/', $dsn, $dbMatches);
        
        $host = $hostMatches[1] ?? 'localhost';
        $dbname = $dbMatches[1] ?? 'mmc_db';

        // Prepare backup file path
        $backupDir = Yii::getAlias('@console/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $dateString = date('Y-m-d_H-i-s');
        $fileName = "backup_{$dbname}_{$dateString}.sql";
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        // Run mysqldump
        // Note: For Linux hosting, mysqldump is usually available in the PATH.
        $mysqldumpPath = 'mysqldump';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $mysqldumpPath = 'e:\xampp\mysql\bin\mysqldump.exe';
        }
        $cmd = "\"{$mysqldumpPath}\" -h {$host} -u {$username}";
        if (!empty($password)) {
            $cmd .= " -p" . escapeshellarg($password); // No space between -p and password
        }
        $cmd .= " {$dbname} > " . escapeshellarg($filePath);

        $this->stdout("Executing mysqldump...\n", Console::FG_YELLOW);
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->stderr("Error: mysqldump failed!\n", Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        $this->stdout("Backup created successfully: {$filePath}\n", Console::FG_GREEN);

        // Compress the SQL file to save space and upload time (optional but recommended)
        $zipFilePath = $filePath . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filePath, $fileName);
            $zip->close();
            // Delete the original .sql file
            unlink($filePath);
            
            $filePath = $zipFilePath;
            $fileName = $fileName . '.zip';
            $this->stdout("Backup compressed: {$filePath}\n", Console::FG_GREEN);
        }

        // Upload to Google Drive
        $this->stdout("Uploading to Google Drive...\n", Console::FG_YELLOW);
        if ($this->uploadToGoogleDrive($filePath, $fileName)) {
            $this->stdout("Upload successful!\n", Console::FG_GREEN);
            
            // Delete local backup after successful upload to save space (Optional)
            unlink($filePath);
            $this->stdout("Local backup file deleted.\n", Console::FG_GREEN);
        } else {
            $this->stderr("Upload failed!\n", Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        $this->stdout("Backup process completed.\n", Console::FG_GREEN);
        return Controller::EXIT_CODE_NORMAL;
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
