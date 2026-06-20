<?php
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email']) && isset($data['password'])) {
    $email = $conn->real_escape_string($data['email']);
    $password_input = $data['password'];

    // Cari user berdasarkan email
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Cek kecocokan password yang diinput dengan password hash di database
        if (password_verify($password_input, $user['password'])) {
            
            // Cek apakah email sudah diverifikasi
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                echo json_encode(["status" => "unverified", "message" => "Email belum diverifikasi. Silakan masukkan kode OTP yang dikirim ke email Anda!"]);
                exit;
            }

            // Hapus password dari array sebelum dikembalikan ke Flutter biar aman
            unset($user['password']);
            
            echo json_encode([
                "status" => "success", 
                "message" => "Login berhasil!", 
                "data" => $user
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Password yang kamu masukkan salah!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Akun dengan email ini tidak ditemukan!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Email dan Password wajib diisi!"]);
}
?>