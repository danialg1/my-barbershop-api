<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'])) {
    $id = $conn->real_escape_string($data['user_id']);
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone']);

    // Query dasar update profil
    $sql = "UPDATE users SET name='$name', email='$email', phone='$phone'";

    // Cek apakah user mengirimkan foto (Base64) baru atau mengosongkannya
    if (isset($data['photoBase64'])) {
        $photo = $conn->real_escape_string($data['photoBase64']);
        $sql .= ", photo='$photo'";
    }

    $sql .= " WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Profil berhasil diperbarui"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal update: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
?>