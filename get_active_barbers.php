<?php
require 'koneksi.php';

$sql = "SELECT id, name, photo FROM users WHERE role = 'barber' AND is_active = 1";
$result = $conn->query($sql);
$barbers = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $photo = $row['photo'];
        
        // Cek jika photo kosong atau default, gunakan placeholder
        if (empty($photo) || $photo === 'default.png') {
            $image_url = 'https://picsum.photos/200?random=' . $row['id'];
        } else if (strpos($photo, 'http') === 0) {
            $image_url = $photo;
        } else if (strlen($photo) > 100) {
            // Assume base64
            $image_url = 'data:image/jpeg;base64,' . $photo;
        } else {
            $image_url = 'http://192.168.1.5/barbershop_api/uploads/' . $photo;
        }

        $barbers[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'role' => 'Professional Barber',
            'exp' => 'Tersedia',
            'image' => $image_url
        ];
    }
    echo json_encode(["status" => "success", "data" => $barbers]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
