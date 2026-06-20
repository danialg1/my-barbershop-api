<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'])) {
    $id = $conn->real_escape_string($data['user_id']);
    
    // Tarik semua data user berdasarkan ID
    $sql = "SELECT * FROM users WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Hitung Kunjungan Dinamis (Total Reservasi Selesai / Terkonfirmasi Lunas)
        $sql_visits = "SELECT COUNT(*) as total_visits FROM reservations WHERE user_id='$id' AND status IN ('completed', 'confirmed')";
        $res_visits = $conn->query($sql_visits);
        $total_visits = 0;
        if ($res_visits && $res_visits->num_rows > 0) {
            $total_visits = $res_visits->fetch_assoc()['total_visits'];
        }
        
        // Hitung Poin Elite Dinamis (Harga Layanan / 10000)
        $sql_points = "SELECT SUM(s.price) as total_spent FROM reservations r JOIN services s ON r.service_id = s.id WHERE r.user_id='$id' AND r.status IN ('completed', 'confirmed')";
        $res_points = $conn->query($sql_points);
        $total_spent = 0;
        if ($res_points && $res_points->num_rows > 0) {
            $total_spent = $res_points->fetch_assoc()['total_spent'] ?? 0;
        }
        
        $earned_points = floor($total_spent / 10000);
        $used_points = isset($user['used_elite_points']) ? (int)$user['used_elite_points'] : 0;
        $current_elite_points = max(0, $earned_points - $used_points);
        
        // Timpa nilai statis dengan nilai dinamis
        $user['visits'] = $total_visits;
        $user['elite_points'] = $current_elite_points;

        // Sembunyikan password
        unset($user['password']);
        echo json_encode(["status" => "success", "data" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "User tidak ditemukan"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "ID tidak diberikan"]);
}
?>