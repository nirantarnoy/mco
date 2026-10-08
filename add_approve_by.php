<?php
$conn = new mysqli("127.0.0.1", "root", "", "mco_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = ['purchase_master', 'purch'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'approve_by'");
    if ($result->num_rows == 0) {
        if ($conn->query("ALTER TABLE `$table` ADD `approve_by` INT NULL DEFAULT NULL")) {
            echo "Added approve_by to $table\n";
        } else {
            echo "Error adding approve_by to $table: " . $conn->error . "\n";
        }
    } else {
        echo "approve_by already exists in $table\n";
    }
}
$conn->close();
echo "Done!";
