<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

// Pastikan request mengirimkan ID Barber dan aksi yang mau dilakukan
if (!isset($data['barber_id']) || !isset($data['action'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

$barber_id = $conn->real_escape_string($data['barber_id']);
$action = $data['action'];

// AKSI 1: MENGAMBIL DAFTAR JADWAL
if ($action == 'get_schedule') {
    // Kita lakukan fungsi JOIN untuk menarik nama pelanggan dan nama layanannya sekaligus
    $sql = "SELECT 
                r.id as reservation_id,
                r.reservation_date,
                r.status,
                u.name as customer_name,
                u.photo as customer_photo,
                s.name as service_name,
                s.price as service_price,
                r.cancel_reason
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN services s ON r.service_id = s.id
            WHERE r.barber_id = '$barber_id' 
            AND r.status IN ('pending', 'confirmed', 'in_progress', 'cancel_requested')
            ORDER BY r.reservation_date ASC";
            
    $result = $conn->query($sql);
    $reservations = [];
    
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $reservations]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }

// AKSI 2: MENGUBAH STATUS PESANAN
} elseif ($action == 'update_status') {
    if (!isset($data['reservation_id']) || !isset($data['new_status'])) {
        echo json_encode(["status" => "error", "message" => "Parameter ID Reservasi atau Status Baru tidak ditemukan"]);
        exit;
    }
    
    $reservation_id = $conn->real_escape_string($data['reservation_id']);
    // Status baru bisa berupa: 'confirmed', 'in_progress', 'completed', atau 'rejected'
    $new_status = $conn->real_escape_string($data['new_status']); 
    
    $sql = "UPDATE reservations SET status='$new_status' WHERE id='$reservation_id' AND barber_id='$barber_id'";
    
    if ($conn->query($sql)) {
        // Ambil user_id untuk dikirim notifikasi
        $cek_res = $conn->query("SELECT user_id FROM reservations WHERE id='$reservation_id'");
        if ($cek_res && $cek_res->num_rows > 0) {
            $user_id = $cek_res->fetch_assoc()['user_id'];
            
            $title = "";
            $msg = "";
            
            if ($new_status == 'confirmed') {
                $title = "Reservasi Diterima";
                $msg = "Hore! Reservasi Anda (Order #$reservation_id) telah diterima oleh Barber. Jangan sampai telat ya!";
            } else if ($new_status == 'rejected') {
                $title = "Reservasi Ditolak";
                $msg = "Mohon maaf, reservasi Anda (Order #$reservation_id) ditolak oleh Barber. Silakan pilih jadwal atau Barber lain.";
            } else if ($new_status == 'completed') {
                $title = "Layanan Selesai";
                $msg = "Layanan cukur Anda telah selesai. Terima kasih telah memilih My Barbershop!";
            }
            
            if ($title != "") {
                $conn->query("INSERT INTO notifications (user_id, title, message, type) VALUES ('$user_id', '$title', '$msg', 'reservation')");
            }
        }
        
        echo json_encode(["status" => "success", "message" => "Status pesanan berhasil diperbarui"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Aksi tidak dikenali"]);
}
?>