<?php
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email']) && isset($data['otp']) && isset($data['new_password'])) {
    $email = $conn->real_escape_string($data['email']);
    $otp = $conn->real_escape_string($data['otp']);
    $new_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
    
    // Pastikan OTP valid sebelum mereset (demi keamanan)
    $sql_check = "SELECT id FROM users WHERE email='$email' AND verification_code='$otp'";
    $result = $conn->query($sql_check);
    
    if ($result->num_rows > 0) {
        // Reset password dan kosongkan verification_code agar tidak bisa dipakai ulang
        $sql_update = "UPDATE users SET password='$new_password', verification_code='' WHERE email='$email'";
        
        if ($conn->query($sql_update) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Password berhasil diubah. Silakan login!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Terjadi kesalahan sistem saat mengubah password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Sesi tidak valid atau OTP salah."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap!"]);
}
?>
