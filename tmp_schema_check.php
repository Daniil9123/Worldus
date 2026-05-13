<?php
$m = new mysqli('localhost','root','','worldus_test');
if ($m->connect_error) {
    echo 'err: ' . $m->connect_error;
    exit(1);
}
$res = $m->query('SHOW COLUMNS FROM levels');
if (!$res) {
    echo 'query error: ' . $m->error;
    exit(1);
}
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
