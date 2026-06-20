<?php
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email']) && isset($data['otp'])) {
    $email = $conn->real_escape_string($data['email']);
    $otp = $conn->real_escape_string($data['otp']);
    
    // Cek apakah email dan OTP cocok
    $sql = "SELECT id FROM users WHERE email='$email' AND verification_code='$otp'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo json_encode(["status" => "success", "message" => "OTP valid, silakan masukkan password baru."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Kode OTP salah!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data email atau OTP tidak diberikan!"]);
}
?>
