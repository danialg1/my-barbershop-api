<?php
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
    exit;
}

$user_id = $conn->real_escape_string($data['user_id']);

// Jika mengirim notification_id, maka mark 1 notifikasi saja. Jika tidak, mark all.
if (isset($data['notification_id'])) {
    $notif_id = $conn->real_escape_string($data['notification_id']);
    $sql = "UPDATE notifications SET is_read=1 WHERE id='$notif_id' AND user_id='$user_id'";
} else {
    $sql = "UPDATE notifications SET is_read=1 WHERE user_id='$user_id'";
}

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Status notifikasi diperbarui"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
