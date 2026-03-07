<?php
$dsn = 'mysql:host=localhost;dbname=mmc_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Bill Placement records in 'invoices' table (mmc_db) for 2026:\n";
    $stmt = $pdo->query("SELECT id, invoice_number, invoice_date, customer_name, status FROM invoices WHERE invoice_type = 'bill_placement' AND invoice_number LIKE 'BP-2026-%' ORDER BY id DESC");
    $rows = $stmt->fetchAll();
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    echo "\nInvoice Sequences (mmc_db):\n";
    $stmt = $pdo->query("SELECT * FROM invoice_sequences WHERE invoice_type='bill_placement'");
    $rows = $stmt->fetchAll();
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
