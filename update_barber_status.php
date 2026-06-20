<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['barber_id']) || !isset($data['is_active'])) {
    echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
    exit;
}

$barber_id = $conn->real_escape_string($data['barber_id']);
$is_active = $conn->real_escape_string($data['is_active']) ? 1 : 0;

$sql = "UPDATE users SET is_active = $is_active WHERE id = '$barber_id' AND role = 'barber'";
if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Status berhasil diperbarui"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
