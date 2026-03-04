<?php
$dsn = 'mysql:host=localhost;dbname=mco_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    
    $sql = "SELECT purch_req_no, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) as num 
            FROM purch_req 
            ORDER BY num DESC 
            LIMIT 20";
    
    $stmt = $pdo->query($sql);
    echo "Top 20 by Sequence Number:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "NO: " . $row['purch_req_no'] . " | Extracted Num: " . $row['num'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
