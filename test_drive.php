<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

$folderId = '1D2ku_jO3r8liyCAZhz0FyCyvjZFdXYdI';
$keyFilePath = __DIR__ . '/console/config/google-key.json';

if (!file_exists($keyFilePath)) {
    die("Error: Google Key file not found at $keyFilePath\n");
}

echo "Authenticating using Service Account...\n";
$client = new Client();
$client->setAuthConfig($keyFilePath);
$client->addScope(Drive::DRIVE_FILE);

$driveService = new Drive($client);

$fileMetadata = new Drive\DriveFile([
    'name' => 'test_upload.txt',
    'parents' => [$folderId]
]);

$content = "This is a test file to verify Google Drive upload.";

echo "Attempting to upload a test file to Folder ID: $folderId ...\n";

try {
    $file = $driveService->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => 'text/plain',
        'uploadType' => 'multipart',
        'fields' => 'id',
        'supportsAllDrives' => true
    ]);
    echo "Success! File uploaded with ID: " . $file->id . "\n";
} catch (\Exception $e) {
    echo "Upload failed with error:\n";
    echo $e->getMessage() . "\n";
}
