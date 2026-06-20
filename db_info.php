<?php
require 'koneksi.php';
$tables = ['users', 'reservations', 'services'];
foreach ($tables as $t) {
    echo "TABLE $t:\n";
    $res = $conn->query("DESCRIBE $t");
    while($row = $res->fetch_assoc()) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
}
?>
