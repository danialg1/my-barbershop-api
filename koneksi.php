<?php
// Izinkan akses dari luar (CORS) agar Flutter bisa nembak API ini
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";       // Username default XAMPP
$pass = "";           // Password default XAMPP (kosong)
$db   = "db_barbershop"; // Nama database yang barusan kamu bikin

// Bikin koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error", 
        "message" => "Koneksi database gagal: " . $conn->connect_error
    ]));
}
?>