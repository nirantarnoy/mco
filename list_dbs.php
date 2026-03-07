<?php
$c = mysqli_connect('localhost', 'root', '');
if (!$c) die("Connection failed: " . mysqli_connect_error());
$r = mysqli_query($c, 'SHOW DATABASES');
while($row = mysqli_fetch_row($r)) {
    echo $row[0] . "\n";
}
