<?php
/**
 * Script to update Quotation invoice numbers to YYYYMMRRR format retroactively.
 * Place this in your project root and run via CLI: php fix_invoice_numbers.php
 */

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');

// Load DB config from Yii2 local settings
$config = require(__DIR__ . '/common/config/main-local.php');
$dbConfig = $config['components']['db'];

// Parse DSN to get host and dbname for PDO
preg_match('/host=([^;]+)/', $dbConfig['dsn'], $hostMatches);
preg_match('/dbname=([^;]+)/', $dbConfig['dsn'], $dbMatches);

$host = $hostMatches[1] ?? 'localhost';
$dbname = $dbMatches[1] ?? '';
$user = $dbConfig['username'];
$pass = $dbConfig['password'];

if (empty($dbname)) {
    die("Error: Could not determine database name from config.\n");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Successfully connected to database: $dbname\n";

    // 1. Fetch all Quotations (ใบแจ้งหนี้) ordered by date and ID
    $stmt = $pdo->prepare("SELECT id, invoice_number, invoice_date FROM invoices WHERE invoice_type = 'quotation' ORDER BY invoice_date ASC, id ASC");
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($invoices) . " quotations to process.\n";

    $pdo->beginTransaction();

    $counters = []; // Track last number per YearMonth

    foreach ($invoices as $row) {
        $year = date('Y', strtotime($row['invoice_date']));
        $month = date('m', strtotime($row['invoice_date']));
        $key = $year . $month;

        if (!isset($counters[$key])) {
            $counters[$key] = 1;
        } else {
            $counters[$key]++;
        }

        // Generate new format: YYYYMMRRR
        $newNumber = $year . $month . str_pad($counters[$key], 3, '0', STR_PAD_LEFT);
        
        echo "Updating ID {$row['id']}: {$row['invoice_number']} -> {$newNumber}\n";
        
        $upStmt = $pdo->prepare("UPDATE invoices SET invoice_number = ? WHERE id = ?");
        $upStmt->execute([$newNumber, $row['id']]);
    }

    // 2. Synchronize invoice_sequences table for monthly resets
    echo "\nSynchronizing sequences...\n";
    foreach ($counters as $key => $lastVal) {
        $yr = substr($key, 0, 4);
        $mn = substr($key, 4, 2);
        
        $chkStmt = $pdo->prepare("SELECT 1 FROM invoice_sequences WHERE invoice_type = 'quotation' AND year = ? AND month = ?");
        $chkStmt->execute([$yr, $mn]);
        $exists = $chkStmt->fetch();
            
        if ($exists) {
            $upSeq = $pdo->prepare("UPDATE invoice_sequences SET last_number = ? WHERE invoice_type = 'quotation' AND year = ? AND month = ?");
            $upSeq->execute([$lastVal, $yr, $mn]);
            echo "Updated sequence for $yr-$mn: Last number is $lastVal\n";
        } else {
            $insSeq = $pdo->prepare("INSERT INTO invoice_sequences (invoice_type, year, month, last_number, prefix) VALUES ('quotation', ?, ?, ?, 'QT')");
            $insSeq->execute([$yr, $mn, $lastVal]);
            echo "Created new sequence for $yr-$mn: Start with $lastVal\n";
        }
    }

    $pdo->commit();
    echo "\nSuccess: All quotations updated successfully.\n";
    echo "You can now safely delete this script.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
