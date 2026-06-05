<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mco_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
CREATE TABLE IF NOT EXISTS pre_advance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NULL,
    pre_advance_no VARCHAR(50) NULL,
    trans_date DATE NULL,
    recipient_name VARCHAR(255) NULL,
    amount DECIMAL(10,2) NULL DEFAULT 0,
    remark TEXT NULL,
    status INT DEFAULT 0,
    created_at INT NULL,
    updated_at INT NULL,
    created_by INT NULL,
    updated_by INT NULL,
    company_id INT NULL,
    UNIQUE(pre_advance_no)
);

CREATE TABLE IF NOT EXISTS pre_advance_line (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pre_advance_id INT NULL,
    line_date DATE NULL,
    description VARCHAR(255) NULL,
    amount DECIMAL(10,2) NULL DEFAULT 0,
    remark TEXT NULL
);

CREATE TABLE IF NOT EXISTS pre_advance_doc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pre_advance_id INT NULL,
    file_name VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    file_size INT NULL,
    uploaded_at INT NULL,
    uploaded_by INT NULL
);

CREATE TABLE IF NOT EXISTS pre_advance_ref (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pre_advance_id INT NULL,
    ref_id INT NULL,
    ref_type INT NULL
);
";

if ($conn->multi_query($sql) === TRUE) {
    echo "Tables created successfully";
} else {
    echo "Error creating tables: " . $conn->error;
}
$conn->close();
?>
