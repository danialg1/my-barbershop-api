<?php
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

$reservation_id = isset($data['reservation_id']) ? $conn->real_escape_string($data['reservation_id']) : '';
$cancel_reason = isset($data['cancel_reason']) ? $conn->real_escape_string($data['cancel_reason']) : '';

if (empty($reservation_id) || empty($cancel_reason)) {
    echo json_encode(["status" => "error", "message" => "ID reservasi dan alasan pembatalan harus diisi"]);
    exit;
}

// Update status to 'cancel_requested' and save the reason
$sql = "UPDATE reservations SET status='cancel_requested', cancel_reason='$cancel_reason' WHERE id='$reservation_id'";

if ($conn->query($sql) === TRUE) {
    // Optional: Fetch reservation details to send specific notifications
    $get_res = "SELECT user_id, barber_id FROM reservations WHERE id='$reservation_id'";
    $res = $conn->query($get_res);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $user_id = $row['user_id'];
        $barber_id = $row['barber_id'];

        // Get customer name
        $user_name = "Pelanggan";
        $u_query = $conn->query("SELECT name FROM users WHERE id='$user_id'");
        if ($u_query && $u_query->num_rows > 0) {
            $user_name = $u_query->fetch_assoc()['name'];
        }

        $message = "$user_name mengajukan pembatalan reservasi (#$reservation_id). Alasan: $cancel_reason";

        // Notify Admins
        $admin_query = $conn->query("SELECT id FROM users WHERE role='admin'");
        while ($admin = $admin_query->fetch_assoc()) {
            $a_id = $admin['id'];
            $conn->query("INSERT INTO notifications (user_id, title, message) VALUES ('$a_id', 'Permintaan Batal Reservasi', '$message')");
        }

        // Notify Barber (if assigned)
        if (!empty($barber_id)) {
            $conn->query("INSERT INTO notifications (user_id, title, message) VALUES ('$barber_id', 'Permintaan Batal Reservasi', '$message')");
        }
    }

    echo json_encode(["status" => "success", "message" => "Permintaan pembatalan berhasil dikirim"]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal mengirim permintaan: " . $conn->error]);
}
?>
