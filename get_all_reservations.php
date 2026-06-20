<?php
require 'koneksi.php';

$sql = "SELECT 
            r.id as reservation_id, 
            r.reservation_date, 
            r.status, 
            u.name as customer_name,
            b.name as barber_name, 
            s.name as service_name, 
            s.price as service_price,
            r.cancel_reason
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN users b ON r.barber_id = b.id
        LEFT JOIN services s ON r.service_id = s.id
        ORDER BY r.reservation_date DESC";

$result = $conn->query($sql);
$reservations = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $reservations]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
