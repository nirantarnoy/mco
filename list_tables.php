<?php
$dbs = ['mco_db', 'mmc_db'];
foreach ($dbs as $db) {
    echo "--- Tables in $db ---\n";
    $c = mysqli_connect('localhost', 'root', '', $db);
    if (!$c) {
        echo "Failed to connect to $db\n";
        continue;
    }
    $r = mysqli_query($c, 'SHOW TABLES');
    while($row = mysqli_fetch_row($r)) {
        echo $row[0] . "\n";
    }
    mysqli_close($c);
    echo "\n";
}
