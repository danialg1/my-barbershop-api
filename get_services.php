<?php
require 'koneksi.php';

$sql = "SELECT * FROM services";
$result = $conn->query($sql);
$services = [];

if ($result) {
    $base_url = "http://192.168.1.5/barbershop_api/uploads/services/";
    while($row = $result->fetch_assoc()) {
        if (!empty($row['image'])) {
            if (strpos($row['image'], 'http') === 0) {
                $row['image_url'] = $row['image'];
            } else {
                $row['image_url'] = $base_url . $row['image'];
            }
        } else {
            $row['image_url'] = $base_url . 'default_service.png';
        }
        $services[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $services]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>