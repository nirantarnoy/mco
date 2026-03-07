<?php
$dsn = 'mysql:host=localhost;dbname=mco_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Checking billing_invoices table structure:\n";
    $stmt = $pdo->query("DESCRIBE billing_invoices");
    while ($row = $stmt->fetch()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }

    echo "\nSearching for BP-2026-006 records:\n";
    $stmt = $pdo->prepare("SELECT id, billing_number, billing_date, customer_id, company_id, total_amount, status FROM billing_invoices WHERE billing_number = 'BP-2026-006'");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    echo "\nRecent records (last 10 by id):\n";
    $stmt = $pdo->query("SELECT id, billing_number, billing_date, company_id FROM billing_invoices ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll();
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Try the server DSN if localhost fails or if requested
    echo "\nTrying server DSN from comments...\n";
    $dsn_server = 'mysql:host=192.168.60.194;dbname=mmc_db';
    try {
        $pdo = new PDO($dsn_server, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo "Connected to server DB!\n";
        // Repeat check here if needed
    } catch (Exception $e2) {
        echo "Server DB Error: " . $e2->getMessage() . "\n";
    }
}
