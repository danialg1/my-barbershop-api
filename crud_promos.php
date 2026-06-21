<?php
header('Content-Type: application/json');
require 'koneksi.php';
require_once 'fcm_helper.php';

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

if ($action === 'read') {
    $result = $conn->query("SELECT * FROM promos ORDER BY id DESC");
    $promos = [];
    while ($row = $result->fetch_assoc()) {
        $promos[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $promos]);
} elseif ($action === 'create') {
    $code = strtoupper($conn->real_escape_string($data['code'] ?? ''));
    $title = $conn->real_escape_string($data['title'] ?? $data['description'] ?? '');
    $discount_percent = (int)($data['discount_percent'] ?? 0);
    $is_active = (int)($data['is_active'] ?? 1);

    if (!empty($code) && !empty($title) && $discount_percent > 0) {
        $check = $conn->query("SELECT id FROM promos WHERE code='$code'");
        if ($check->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Kode promo sudah ada']);
        } else {
            if ($conn->query("INSERT INTO promos (code, title, discount_percent, is_active) VALUES ('$code', '$title', $discount_percent, $is_active)")) {
                
                // Trigger FCM ke semua pelanggan jika promo aktif
                if ($is_active === 1) {
                    $custTokens = getAllCustomerTokens($conn);
                    sendFCMNotification($custTokens, 'Promo Baru!', "Diskon $discount_percent% dengan kode $code. Yuk pesan sekarang!", ['type' => 'promo']);
                }

                echo json_encode(['status' => 'success', 'message' => 'Promo berhasil ditambahkan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan promo']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    }
} elseif ($action === 'update') {
    $id = (int)($data['id'] ?? 0);
    $code = strtoupper($conn->real_escape_string($data['code'] ?? ''));
    $title = $conn->real_escape_string($data['title'] ?? $data['description'] ?? '');
    $discount_percent = (int)($data['discount_percent'] ?? 0);
    $is_active = (int)($data['is_active'] ?? 1);

    if ($id > 0 && !empty($code) && !empty($title) && $discount_percent > 0) {
        $check = $conn->query("SELECT id FROM promos WHERE code='$code' AND id != $id");
        if ($check->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Kode promo sudah digunakan']);
        } else {
            if ($conn->query("UPDATE promos SET code='$code', title='$title', discount_percent=$discount_percent, is_active=$is_active WHERE id=$id")) {
                echo json_encode(['status' => 'success', 'message' => 'Promo berhasil diperbarui']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui promo']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    }
} elseif ($action === 'delete') {
    $id = (int)($data['id'] ?? 0);
    if ($id > 0) {
        if ($conn->query("DELETE FROM promos WHERE id=$id")) {
            echo json_encode(['status' => 'success', 'message' => 'Promo berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus promo']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
}
?>
