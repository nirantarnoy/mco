<?php
require __DIR__ . '/../../vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

$clientSecretPath = __DIR__ . '/client_secret.json';
$tokenPath = __DIR__ . '/token.json';

if (!file_exists($clientSecretPath)) {
    die("Error: client_secret.json not found at $clientSecretPath\nPlease download it from Google Cloud Console.\n");
}

$client = new Client();
$client->setAuthConfig($clientSecretPath);
$client->addScope(Drive::DRIVE_FILE);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
$client->setRedirectUri('http://127.0.0.1:8080');

// Request authorization from the user.
// Check if redirect_uris contains urn:ietf:wg:oauth:2.0:oob or http://localhost
$authUrl = $client->createAuthUrl();
printf("\n=================================================================\n");
printf("1. เปิดลิงก์นี้ในเบราว์เซอร์เพื่อเข้าสู่ระบบ Google และกดยอมรับสิทธิ์:\n\n%s\n\n", $authUrl);
printf("2. หลังจากกดยอมรับ ให้คัดลอก 'code' (หรือ URL ทั้งหมดหลังการ redirect) มาวางด้านล่าง\n");
printf("=================================================================\n");
print 'วาง code หรือ URL ที่ได้ที่นี่: ';
$input = trim(fgets(STDIN));

// ถ้าผู้ใช้วางมาเป็น Full URL เช่น http://localhost/?code=4/0A... ให้ตัดเอาเฉพาะ code
if (strpos($input, 'code=') !== false) {
    parse_str(parse_url($input, PHP_URL_QUERY), $queryParams);
    $authCode = $queryParams['code'] ?? $input;
} else {
    $authCode = $input;
}

// Exchange authorization code for an access token.
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
$client->setAccessToken($accessToken);

// Check to see if there was an error.
if (array_key_exists('error', $accessToken)) {
    throw new Exception(join(', ', $accessToken));
}

// Save the token to a file.
if (!file_exists(dirname($tokenPath))) {
    mkdir(dirname($tokenPath), 0700, true);
}
file_put_contents($tokenPath, json_encode($client->getAccessToken()));
echo "Credentials saved to $tokenPath\n";
echo "You can now run the backup command!\n";
