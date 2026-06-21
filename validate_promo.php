<?php
header('Content-Type: application/json');
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
$code = strtoupper($conn->real_escape_string($data['code'] ?? ''));

if (!empty($code)) {
    $result = $conn->query("SELECT * FROM promos WHERE code='$code' AND is_active=1 LIMIT 1");
    if ($result->num_rows > 0) {
        $promo = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $promo]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kode promo tidak valid atau sudah tidak aktif']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Kode promo kosong']);
}
?>
