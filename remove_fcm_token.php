<?php
require_once 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['user_id'] ?? '';

if (empty($userId)) {
    echo json_encode(["status" => "error", "message" => "user_id required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET fcm_token = NULL WHERE id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "FCM token removed"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to remove FCM token"]);
}

$stmt->close();
$conn->close();
?>
