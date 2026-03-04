<?php
$dsn = 'mysql:host=localhost;dbname=mco_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    
    $sql = "SELECT id, purch_req_no, company_id, 
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) as extracted_num 
            FROM purch_req 
            ORDER BY extracted_num DESC 
            LIMIT 5";
    
    $stmt = $pdo->query($sql);
    echo "--- RECORDS CAUSING HIGH MAX ---\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | NO: {$row['purch_req_no']} | CO: {$row['company_id']} | Extracted: {$row['extracted_num']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
