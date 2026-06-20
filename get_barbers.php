<?php
require 'koneksi.php';

$sql = "SELECT id, name FROM users WHERE role = 'barber'";
$result = $conn->query($sql);
$barbers = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $barbers[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $barbers]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
