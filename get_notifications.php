<?php
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "user_id tidak ditemukan"]);
    exit;
}

$user_id = $conn->real_escape_string($data['user_id']);

// 1. Cek apakah ada jadwal dalam 24 jam ke depan (khusus customer)
// Ambil role user
$user_sql = "SELECT role FROM users WHERE id='$user_id'";
$user_res = $conn->query($user_sql);
if ($user_res && $user_res->num_rows > 0) {
    $user_data = $user_res->fetch_assoc();
    if ($user_data['role'] == 'customer') {
        // Cari reservasi confirmed < 24 jam
        $cek_jadwal = "SELECT id, reservation_date, status FROM reservations 
                       WHERE user_id='$user_id' 
                       AND status='confirmed' 
                       AND reservation_date >= NOW() 
                       AND reservation_date <= DATE_ADD(NOW(), INTERVAL 24 HOUR)";
        $res_jadwal = $conn->query($cek_jadwal);
        
        if ($res_jadwal && $res_jadwal->num_rows > 0) {
            // Kita temukan jadwal dekat! Cek apakah notifikasi pengingat sudah dikirim (berdasarkan title unik)
            while ($row = $res_jadwal->fetch_assoc()) {
                $res_id = $row['id'];
                $res_date = $row['reservation_date'];
                
                $title = "Pengingat Jadwal (Order #$res_id)";
                $cek_notif = "SELECT id FROM notifications WHERE user_id='$user_id' AND title='$title'";
                $res_notif = $conn->query($cek_notif);
                
                // Kalau belum ada notif untuk pengingat ini, kita insert
                if ($res_notif && $res_notif->num_rows == 0) {
                    $message = "Halo! Anda memiliki jadwal reservasi besok/hari ini pada " . $res_date . ". Jangan sampai telat ya!";
                    $insert_notif = "INSERT INTO notifications (user_id, title, message, type, is_read) 
                                     VALUES ('$user_id', '$title', '$message', 'reservation', 0)";
                    $conn->query($insert_notif);
                }
            }
        }
    }
}

// 2. Ambil semua notifikasi untuk user ini, urutkan dari yang terbaru
$sql = "SELECT * FROM notifications WHERE user_id='$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

echo json_encode(["status" => "success", "data" => $notifications]);
?>
