<?php
require 'koneksi.php';

$stats = [
    'total_income' => 0,
    'total_customers' => 0,
    'total_barbers' => 0
];

// 1. Total Pendapatan
$sql_income = "SELECT SUM(s.price) as total_income FROM reservations r JOIN services s ON r.service_id = s.id WHERE r.status = 'completed'";
$result_income = $conn->query($sql_income);
if ($result_income && $result_income->num_rows > 0) {
    $row = $result_income->fetch_assoc();
    $stats['total_income'] = (int)$row['total_income'];
}

// 2. Jumlah Pelanggan
$sql_customers = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
$result_customers = $conn->query($sql_customers);
if ($result_customers && $result_customers->num_rows > 0) {
    $row = $result_customers->fetch_assoc();
    $stats['total_customers'] = (int)$row['count'];
}

// 3. Jumlah Barber
$sql_barbers = "SELECT COUNT(*) as count FROM users WHERE role = 'barber'";
$result_barbers = $conn->query($sql_barbers);
if ($result_barbers && $result_barbers->num_rows > 0) {
    $row = $result_barbers->fetch_assoc();
    $stats['total_barbers'] = (int)$row['count'];
}

echo json_encode([
    "status" => "success",
    "data" => $stats
]);
?>
