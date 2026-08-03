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
$authUrl = $client->createAuthUrl();
printf("Open the following link in your browser:\n%s\n", $authUrl);
print 'Enter verification code: ';
$authCode = trim(fgets(STDIN));

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
