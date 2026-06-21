<?php
require 'koneksi.php';
require_once 'fcm_helper.php';

// Menangkap laporan dari Midtrans
$json_result = file_get_contents('php://input');
$result = json_decode($json_result, true);

if ($result) {
    $order_id = $result['order_id'];
    $transaction_status = $result['transaction_status'];

    // Kita ekstrak ID database dari string 'ORDER-5-1718...'
    $parts = explode('-', $order_id);
    if (count($parts) >= 2) {
        $db_reservation_id = $parts[1]; 

        // Cari user_id dan barber_id dari reservasi
        $cek_res = $conn->query("SELECT user_id, barber_id FROM reservations WHERE id='$db_reservation_id'");
        $user_id = null;
        $barber_id = null;
        if ($cek_res && $cek_res->num_rows > 0) {
            $row_res = $cek_res->fetch_assoc();
            $user_id = $row_res['user_id'];
            $barber_id = $row_res['barber_id'];
        }

        // Jika statusnya lunas / sukses
        if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
            $sql = "UPDATE reservations SET status='confirmed' WHERE id='$db_reservation_id'";
            $conn->query($sql);
            
            // Insert Notifikasi untuk Pelanggan
            if ($user_id) {
                $msg = "Pembayaran Anda untuk Order #$db_reservation_id telah lunas. Reservasi berhasil dikonfirmasi!";
                $conn->query("INSERT INTO notifications (user_id, title, message, type) VALUES ('$user_id', 'Pembayaran Berhasil', '$msg', 'payment')");
                
                $custTokens = getTokensByUserId($conn, $user_id);
                sendFCMNotification($custTokens, 'Pembayaran Berhasil', $msg, ['type' => 'payment']);
            }
            // Insert Notifikasi untuk Barber
            if ($barber_id) {
                $msg_b = "Ada pembayaran lunas untuk Order #$db_reservation_id. Silakan periksa jadwal Anda.";
                $conn->query("INSERT INTO notifications (user_id, title, message, type) VALUES ('$barber_id', 'Reservasi Baru Terbayar', '$msg_b', 'payment')");
                
                $barberTokens = getTokensByUserId($conn, $barber_id);
                sendFCMNotification($barberTokens, 'Reservasi Baru Terbayar', $msg_b, ['type' => 'payment']);
            }
            // Notifikasi ke Admin
            $msg_admin = "Pembayaran lunas untuk Order #$db_reservation_id.";
            $adminTokens = getAdminTokens($conn);
            sendFCMNotification($adminTokens, 'Reservasi Baru Terbayar', $msg_admin, ['type' => 'payment']);

        } 
        else if ($transaction_status == 'cancel' || $transaction_status == 'expire') {
            // Cek apakah reservasi ini menggunakan diskon
            $cek_disc = $conn->query("SELECT discount_applied, user_id FROM reservations WHERE id='$db_reservation_id'");
            if ($cek_disc && $cek_disc->num_rows > 0) {
                $row_disc = $cek_disc->fetch_assoc();
                if ($row_disc['discount_applied'] == 1 && $row_disc['user_id']) {
                    // Kembalikan 25 poin
                    $uid = $row_disc['user_id'];
                    $conn->query("UPDATE users SET used_elite_points = GREATEST(0, used_elite_points - 25) WHERE id='$uid'");
                }
            }

            $sql = "UPDATE reservations SET status='cancelled' WHERE id='$db_reservation_id'";
            $conn->query($sql);
            
            if ($user_id) {
                $msg = "Pembayaran untuk Order #$db_reservation_id telah dibatalkan atau kedaluwarsa.";
                $conn->query("INSERT INTO notifications (user_id, title, message, type) VALUES ('$user_id', 'Pembayaran Gagal', '$msg', 'payment')");
                
                $custTokens = getTokensByUserId($conn, $user_id);
                sendFCMNotification($custTokens, 'Pembayaran Gagal', $msg, ['type' => 'payment']);
            }
        }
    }
}
// Beri tahu Midtrans kalau laporannya sudah diterima dengan baik
http_response_code(200);
echo "OK";
?>