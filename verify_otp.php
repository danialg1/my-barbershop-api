<?php
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email']) && isset($data['otp_code'])) {
    $email = $conn->real_escape_string($data['email']);
    $otp_code = $conn->real_escape_string($data['otp_code']);

    // Cari user
    $sql = "SELECT id, is_verified, verification_code FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['is_verified'] == 1) {
            echo json_encode(["status" => "error", "message" => "Email ini sudah terverifikasi sebelumnya!"]);
            exit;
        }

        if ($user['verification_code'] === $otp_code) {
            // Update status menjadi terverifikasi
            $update_sql = "UPDATE users SET is_verified=1, verification_code=NULL WHERE email='$email'";
            if ($conn->query($update_sql) === TRUE) {
                echo json_encode(["status" => "success", "message" => "Verifikasi berhasil! Silakan login."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal memperbarui status verifikasi."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Kode OTP salah! Coba lagi."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Akun tidak ditemukan!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data email dan OTP wajib dikirim!"]);
}
?>
