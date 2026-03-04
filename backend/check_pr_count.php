<?php
$dsn = 'mysql:host=localhost;dbname=mco_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    
    $stmt = $pdo->query("SELECT count(*) FROM purch_req");
    echo "Count: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $pdo->query("SELECT purch_req_no FROM purch_req ORDER BY id DESC LIMIT 20");
    echo "Last 20 by ID:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['purch_req_no'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
