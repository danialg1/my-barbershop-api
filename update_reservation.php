<?php
require 'koneksi.php';
require_once 'fcm_helper.php';

$data = json_decode(file_get_contents("php://input"), true);

$reservation_id = isset($data['reservation_id']) ? $conn->real_escape_string($data['reservation_id']) : '';
$action = isset($data['action']) ? $conn->real_escape_string($data['action']) : ''; // 'approve_cancel' or 'reject_cancel'

if (empty($reservation_id) || empty($action)) {
    echo json_encode(["status" => "error", "message" => "ID reservasi dan aksi harus diisi"]);
    exit;
}

// Ensure the reservation is actually in cancel_requested state
$check = $conn->query("SELECT user_id, status FROM reservations WHERE id='$reservation_id'");
if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    if ($row['status'] !== 'cancel_requested') {
        echo json_encode(["status" => "error", "message" => "Reservasi ini tidak sedang dalam pengajuan pembatalan"]);
        exit;
    }
    
    $user_id = $row['user_id'];
    $new_status = '';
    $title = '';
    $msg = '';

    if ($action == 'approve_cancel') {
        $new_status = 'cancelled';
        $title = "Pembatalan Disetujui";
        $msg = "Permintaan pembatalan reservasi Anda (Order #$reservation_id) telah disetujui.";
    } else if ($action == 'reject_cancel') {
        // Revert back to pending (or could be confirmed, but pending is safe)
        $new_status = 'pending'; 
        $title = "Pembatalan Ditolak";
        $msg = "Maaf, permintaan pembatalan reservasi Anda (Order #$reservation_id) ditolak oleh Admin. Reservasi tetap aktif.";
    } else {
        echo json_encode(["status" => "error", "message" => "Aksi tidak valid"]);
        exit;
    }

    $sql = "UPDATE reservations SET status='$new_status' WHERE id='$reservation_id'";
    if ($conn->query($sql)) {
        // Notify user
        $conn->query("INSERT INTO notifications (user_id, title, message) VALUES ('$user_id', '$title', '$msg')");
        
        $userTokens = getTokensByUserId($conn, $user_id);
        sendFCMNotification($userTokens, $title, $msg, ['type' => 'reservation']);
        
        echo json_encode(["status" => "success", "message" => "Status pembatalan berhasil diperbarui"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal update: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Reservasi tidak ditemukan"]);
}
?>
