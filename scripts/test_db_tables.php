<?php
$h = "localhost"; $u = "root"; $p = ""; $d = "mco_db";
$c = mysqli_connect($h, $u, $p, $d);
if (!$c) die("Connect error: " . mysqli_connect_error());

$res = mysqli_query($c, "SHOW TABLES");
echo "Total tables: " . mysqli_num_rows($res) . "\n";
$tables = [];
while ($row = mysqli_fetch_array($res)) {
    $tables[] = $row[0];
}
echo implode(", ", array_slice($tables, 0, 15)) . "\n";
