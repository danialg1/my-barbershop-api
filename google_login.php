<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['name'])) {
    echo json_encode(["status" => "error", "message" => "Data Google tidak lengkap"]);
    exit;
}

$email = $conn->real_escape_string($data['email']);
$name = $conn->real_escape_string($data['name']);
$photo = isset($data['photoUrl']) ? $conn->real_escape_string($data['photoUrl']) : 'default.png';

// 1. Cek apakah email sudah terdaftar di database
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 2A. Kalau sudah ada, langsung berikan akses Login
    $user = $result->fetch_assoc();
    echo json_encode(["status" => "success", "message" => "Login Google berhasil", "data" => $user]);
} else {
    // 2B. Kalau belum ada, Daftarkan otomatis sebagai Customer!
    // Buat password acak yang panjang karena mereka tidak akan pakai password ini
    $random_password = password_hash(uniqid() . rand(1000, 9999), PASSWORD_DEFAULT); 
    
    $insertSql = "INSERT INTO users (name, email, password, role, photo) 
                  VALUES ('$name', '$email', '$random_password', 'customer', '$photo')";
    
    if ($conn->query($insertSql)) {
        $newUserId = $conn->insert_id;
        $newUserSql = "SELECT * FROM users WHERE id=$newUserId";
        $newUserResult = $conn->query($newUserSql);
        $newUser = $newUserResult->fetch_assoc();
        
        echo json_encode(["status" => "success", "message" => "Registrasi & Login Google berhasil", "data" => $newUser]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mendaftar otomatis: " . $conn->error]);
    }
}
?>