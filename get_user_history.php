<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User ID tidak ditemukan"]);
    exit;
}

$user_id = $conn->real_escape_string($data['user_id']);

// Kita JOIN ke tabel users (untuk nama barber) dan services (untuk nama layanan & harga)
$sql = "SELECT 
            r.id as reservation_id, 
            r.reservation_date, 
            r.status, 
            b.name as barber_name, 
            s.name as service_name, 
            s.price as service_price,
            r.cancel_reason
        FROM reservations r
        LEFT JOIN users b ON r.barber_id = b.id
        LEFT JOIN services s ON r.service_id = s.id
        WHERE r.user_id = '$user_id'
        ORDER BY r.reservation_date DESC";

$result = $conn->query($sql);
$history = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $history]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>