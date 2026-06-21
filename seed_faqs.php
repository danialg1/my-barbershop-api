<?php
require 'koneksi.php';

$conn->query("CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("TRUNCATE TABLE faqs");

$faqs = [
    [
        'Bagaimana cara membatalkan reservasi?',
        'Anda dapat membatalkan reservasi paling lambat 2 jam sebelum jadwal dimulai melalui menu Riwayat Transaksi. Biaya pembayaran Anda akan dikembalikan sesuai ketentuan yang berlaku.'
    ],
    [
        'Bagaimana cara menggunakan Poin Elite?',
        'Poin Elite dapat ditukarkan secara otomatis saat Anda melakukan pembayaran. Anda bisa memotong total biaya layanan menggunakan akumulasi poin yang Anda miliki.'
    ],
    [
        'Apakah saya bisa datang terlambat?',
        'Toleransi keterlambatan adalah 15 menit dari jadwal reservasi. Lebih dari itu, barber berhak melayani pelanggan berikutnya dan Anda akan dijadwalkan ulang sesuai ketersediaan.'
    ],
    [
        'Metode pembayaran apa saja yang tersedia?',
        'Kami menerima pembayaran Tunai, QRIS, Transfer Bank (BCA, Mandiri), dan E-Wallet (GoPay, OVO, Dana).'
    ]
];

foreach ($faqs as $f) {
    $q = $conn->real_escape_string($f[0]);
    $a = $conn->real_escape_string($f[1]);
    $conn->query("INSERT INTO faqs (question, answer) VALUES ('$q', '$a')");
}

echo "FAQs seeded successfully.";
?>
