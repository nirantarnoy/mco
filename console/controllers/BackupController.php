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
    public $googleDriveFolderId = '1D2ku_jO3r8liyCAZhz0FyCyvjZFdXYdI';

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
        $cmd = "mysqldump -h {$host} -u {$username}";
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
     * Uploads a file to Google Drive using a Service Account.
     */
    protected function uploadToGoogleDrive($filePath, $fileName)
    {
        $keyFilePath = Yii::getAlias('@console/config/google-key.json');
        if (!file_exists($keyFilePath)) {
            $this->stderr("Google Service Account Key not found at: {$keyFilePath}\n", Console::FG_RED);
            return false;
        }

        try {
            $client = new Client();
            $client->setAuthConfig($keyFilePath);
            $client->addScope(Drive::DRIVE_FILE);
            
            $driveService = new Drive($client);

            $fileMetadata = new Drive\DriveFile([
                'name' => $fileName
            ]);
            
            if (!empty($this->googleDriveFolderId)) {
                $fileMetadata->setParents([$this->googleDriveFolderId]);
            }

            // Fallback for mime_content_type if it doesn't exist
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
