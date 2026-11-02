<?php
$h = "localhost"; $u = "root"; $p = ""; $d = "mco_db";
$c = mysqli_connect($h, $u, $p, $d);
if (!$c) die("Connect error: " . mysqli_connect_error());

$res = mysqli_query($c, "SELECT table_name, table_comment 
                        FROM information_schema.tables 
                        WHERE table_schema = '$d' 
                        ORDER BY table_name ASC");

$tables = [];
while ($row = mysqli_fetch_assoc($res)) {
    $tables[] = [
        'name' => $row['table_name'],
        'comment' => $row['table_comment']
    ];
}

file_put_contents(__DIR__ . '/all_db_tables.json', json_encode($tables, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Exported " . count($tables) . " tables to all_db_tables.json\n";
