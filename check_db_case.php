<?php
$h = "localhost"; $u = "root"; $p = ""; $d = "mco_db";
$c = mysqli_connect($h, $u, $p, $d);
if (!$c) die("Connection failed");
$r = mysqli_query($c, "SELECT name, type FROM auth_item WHERE name LIKE '%purch%' OR name LIKE '%action%' LIMIT 20");
echo "DB_NAMES:\n";
while($row = mysqli_fetch_assoc($r)) {
    echo $row['name'] . " (Type: " . $row['type'] . ")\n";
}
mysqli_close($c);
