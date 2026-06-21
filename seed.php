<?php
require 'koneksi.php';
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE services");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
$services = [
    ['Basic Haircut', 'Potong rambut standar + styling pomade/hair powder', 45000],
    ['Premium Haircut Combo', 'Potong rambut + cuci rambut + pijat relax + handuk hangat + styling', 60000],
    ['Kids Haircut', 'Potongan khusus anak-anak (di bawah 10 tahun)', 35000],
    ['Beard Trim / Shave', 'Merapikan jenggot/kumis + pijat wajah ringan', 25000],
    ['Black Hair Coloring', 'Semir warna hitam untuk menyamarkan uban secara instan', 75000],
    ['Fashion Hair Coloring', 'Pewarnaan rambut tren anak muda (seperti ash grey, blonde, cokelat)', 150000],
    ['Hair Wash & Massage', 'Layanan cuci rambut saja + pijat kepala relaksasi', 20000],
    ['Creambath / Hair Spa', 'Perawatan kulit kepala anti ketombe menggunakan produk krim premium', 65000]
];
foreach ($services as $s) {
    $name = $conn->real_escape_string($s[0]);
    $desc = $conn->real_escape_string($s[1]);
    $price = $s[2];
    $conn->query("INSERT INTO services (name, description, price, image) VALUES ('$name', '$desc', $price, 'default_service.png')");
}
echo "Database seeded successfully.";
?>
