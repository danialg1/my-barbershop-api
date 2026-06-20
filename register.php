<?php
require 'koneksi.php';
require 'mail_config.php';

// Menangkap data JSON yang dikirim oleh aplikasi Flutter
$data = json_decode(file_get_contents("php://input"), true);

// Pastikan data tidak kosong
if (isset($data['name']) && isset($data['email']) && isset($data['password'])) {
    
    // Bersihkan inputan agar aman dari serangan Hacker (SQL Injection)
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone'] ?? '');
    
    // HASH PASSWORD! Jangan pernah nyimpan password telanjang di database
    $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);

    // Cek apakah email sudah terdaftar sebelumnya
    $cek_email = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($cek_email->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email ini sudah terdaftar!"]);
    } else {
        // Generate OTP 6 digit
        $otp_code = sprintf("%06d", mt_rand(1, 999999));
        
        // Simpan data dengan is_verified = 0
        $sql = "INSERT INTO users (name, email, password, phone, is_verified, verification_code) 
                VALUES ('$name', '$email', '$password_hashed', '$phone', 0, '$otp_code')";
                
        if ($conn->query($sql) === TRUE) {
            // Kirim OTP via Email
            $email_sent = sendOTP($email, $otp_code);
            
            if ($email_sent) {
                echo json_encode(["status" => "success", "message" => "Akun berhasil dibuat. Kode OTP telah dikirim ke email Anda!"]);
            } else {
                // Jika gagal kirim email, beri tahu tapi akun tetap terbuat (bisa resend nanti)
                echo json_encode(["status" => "success_no_email", "message" => "Akun dibuat, tetapi gagal mengirim email OTP. Pastikan email Anda valid!"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan data ke database."]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data yang dikirim tidak lengkap!"]);
}
?>