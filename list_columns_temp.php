<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=mco_db', 'root', '');
    $tables = ['purch_req_line', 'purch_line'];
    foreach ($tables as $table) {
        echo "--- $table ---\n";
        $stmt = $db->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Field: " . $row['Field'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
