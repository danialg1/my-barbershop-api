<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

// Pastikan ada request yang masuk
if (!isset($data['user_id']) || !isset($data['action'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

$user_id = $conn->real_escape_string($data['user_id']);
$action = $data['action'];

if ($action == 'get') {
    // READ: Ambil semua alamat milik user ini
    $sql = "SELECT * FROM addresses WHERE user_id='$user_id' ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $addresses = [];
    while($row = $result->fetch_assoc()) {
        $addresses[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $addresses]);

} elseif ($action == 'add') {
    // CREATE: Tambah alamat baru
    $address = $conn->real_escape_string($data['address']);
    $sql = "INSERT INTO addresses (user_id, address) VALUES ('$user_id', '$address')";
    if($conn->query($sql)) echo json_encode(["status" => "success"]);
    else echo json_encode(["status" => "error", "message" => $conn->error]);

} elseif ($action == 'update') {
    // UPDATE: Edit alamat yang sudah ada
    $address_id = $conn->real_escape_string($data['address_id']);
    $address = $conn->real_escape_string($data['address']);
    $sql = "UPDATE addresses SET address='$address' WHERE id='$address_id' AND user_id='$user_id'";
    if($conn->query($sql)) echo json_encode(["status" => "success"]);
    else echo json_encode(["status" => "error"]);

} elseif ($action == 'delete') {
    // DELETE: Hapus alamat
    $address_id = $conn->real_escape_string($data['address_id']);
    $sql = "DELETE FROM addresses WHERE id='$address_id' AND user_id='$user_id'";
    if($conn->query($sql)) echo json_encode(["status" => "success"]);
    else echo json_encode(["status" => "error"]);
}
?>