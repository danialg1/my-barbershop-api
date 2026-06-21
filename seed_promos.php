<?php
require 'koneksi.php';

$conn->query("CREATE TABLE IF NOT EXISTS promos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    discount_percent INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("TRUNCATE TABLE promos");

$promos = [
    ['BARBER20', 'Diskon 20% Potong Rambut Pertama', 20],
    ['STYLISH10', 'Diskon 10% Semua Layanan', 10],
    ['GANTENG50', 'Promo Gila 50% Akhir Pekan', 50]
];

foreach ($promos as $p) {
    $code = $conn->real_escape_string($p[0]);
    $title = $conn->real_escape_string($p[1]);
    $discount = (int)$p[2];
    $conn->query("INSERT INTO promos (code, title, discount_percent) VALUES ('$code', '$title', $discount)");
}

echo "Promos seeded successfully.";
?>
