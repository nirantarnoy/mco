<?php
// Script to inspect tables and check comments vs Yii attributeLabels
$h = "localhost"; $u = "root"; $p = ""; $d = "mco_db";
$c = mysqli_connect($h, $u, $p, $d);
if (!$c) die("Connect error: " . mysqli_connect_error());

$res = mysqli_query($c, "SELECT table_name, column_name, column_comment, data_type 
                        FROM information_schema.columns 
                        WHERE table_schema = '$d' AND column_comment != '' LIMIT 20");
echo "Sample comments from DB:\n";
while ($row = mysqli_fetch_assoc($res)) {
    echo "{$row['table_name']}.{$row['column_name']} : {$row['column_comment']}\n";
}
