<?php
require 'koneksi.php';
require 'mail_config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email'])) {
    $email = $conn->real_escape_string($data['email']);
    
    $cek_email = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($cek_email->num_rows > 0) {
        $otp_code = sprintf("%06d", mt_rand(1, 999999));
        
        $sql = "UPDATE users SET verification_code='$otp_code' WHERE email='$email'";
        
        if ($conn->query($sql) === TRUE) {
            $email_sent = sendOTP($email, $otp_code);
            if ($email_sent) {
                echo json_encode(["status" => "success", "message" => "Kode OTP baru telah dikirim ke email Anda!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal mengirim email OTP. Pastikan email Anda valid!"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan OTP baru ke database."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email tidak terdaftar!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data yang dikirim tidak lengkap!"]);
}
?>
