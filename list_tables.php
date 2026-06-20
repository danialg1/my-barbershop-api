<?php
require 'koneksi.php';
$res = $conn->query("SHOW CREATE TABLE services");
echo $res->fetch_assoc()['Create Table'];
echo "\n\n";
$res2 = $conn->query("SHOW CREATE TABLE users");
echo $res2->fetch_assoc()['Create Table'];
?>
