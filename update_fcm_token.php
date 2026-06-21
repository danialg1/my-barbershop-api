<?php
require_once 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['user_id'] ?? '';
$fcmToken = $data['fcm_token'] ?? '';

if (empty($userId) || empty($fcmToken)) {
    echo json_encode(["status" => "error", "message" => "user_id and fcm_token required"]);
    exit;
}

$stmt1 = $conn->prepare("UPDATE users SET fcm_token = NULL WHERE fcm_token = ?");
$stmt1->bind_param("s", $fcmToken);
$stmt1->execute();
$stmt1->close();

$stmt2 = $conn->prepare("UPDATE users SET fcm_token = ? WHERE id = ?");
$stmt2->bind_param("si", $fcmToken, $userId);

if ($stmt2->execute()) {
    echo json_encode(["status" => "success", "message" => "FCM token updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update FCM token"]);
}

$stmt2->close();
$conn->close();
?>
