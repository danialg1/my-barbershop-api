<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['barber_id'])) {
    echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
    exit;
}

$barber_id = $conn->real_escape_string($data['barber_id']);

// Get is_active status
$sql_status = "SELECT is_active FROM users WHERE id = '$barber_id' AND role = 'barber'";
$result_status = $conn->query($sql_status);
$is_active = 0;
if ($result_status && $result_status->num_rows > 0) {
    $row = $result_status->fetch_assoc();
    $is_active = (int)$row['is_active'];
}

// Get monthly income
$sql_income = "SELECT SUM(s.price) as monthly_income 
               FROM reservations r 
               JOIN services s ON r.service_id = s.id 
               WHERE r.barber_id = '$barber_id' 
               AND r.status = 'completed' 
               AND MONTH(r.reservation_date) = MONTH(CURRENT_DATE()) 
               AND YEAR(r.reservation_date) = YEAR(CURRENT_DATE())";
$result_income = $conn->query($sql_income);
$monthly_income = 0;
if ($result_income && $result_income->num_rows > 0) {
    $row = $result_income->fetch_assoc();
    $monthly_income = (int)$row['monthly_income'];
}

echo json_encode([
    "status" => "success", 
    "data" => [
        "is_active" => $is_active == 1,
        "monthly_income" => $monthly_income
    ]
]);
?>
