<?php
$db = mysqli_connect("127.0.0.1", "root", "", "mco_db");
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
$res = mysqli_query($db, "SELECT id, is_vat FROM purch ORDER BY id DESC LIMIT 5");
if (!$res) {
    echo mysqli_error($db);
} else {
    while($row = mysqli_fetch_assoc($res)){ print_r($row); }
}
