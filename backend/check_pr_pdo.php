<?php
$dsn = 'mysql:host=localhost;dbname=mco_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT id, purch_req_no, company_id, created_at 
            FROM purch_req 
            ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) DESC 
            LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "--- START RESULTS ---\n";
    foreach ($rows as $row) {
        $date = $row['created_at'] ? date('Y-m-d H:i:s', $row['created_at']) : 'N/A';
        echo "ID: " . str_pad($row['id'], 6) . " | NO: " . str_pad($row['purch_req_no'], 30) . " | CO: " . $row['company_id'] . " | Date: " . $date . "\n";
    }
    echo "--- END RESULTS ---\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
