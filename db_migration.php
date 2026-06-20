<?php
require 'koneksi.php';

// Menambahkan kolom used_elite_points pada tabel users
$sql1 = "ALTER TABLE users ADD used_elite_points INT(11) DEFAULT 0 AFTER elite_points";
if ($conn->query($sql1) === TRUE) {
    echo "Kolom used_elite_points berhasil ditambahkan ke tabel users.\n";
} else {
    echo "Error menambahkan used_elite_points (mungkin sudah ada): " . $conn->error . "\n";
}

// Menambahkan kolom discount_applied pada tabel reservations
$sql2 = "ALTER TABLE reservations ADD discount_applied TINYINT(1) DEFAULT 0";
if ($conn->query($sql2) === TRUE) {
    echo "Kolom discount_applied berhasil ditambahkan ke tabel reservations.\n";
} else {
    echo "Error menambahkan discount_applied (mungkin sudah ada): " . $conn->error . "\n";
}

$conn->close();
?>
