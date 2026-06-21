<?php
function sendFCMNotification($tokens, $title, $body, $data = []) {
    if (empty($tokens)) return false;

    $url = 'http://localhost:3000/send';
    $postData = [
        'tokens' => is_array($tokens) ? $tokens : [$tokens],
        'title' => $title,
        'body' => $body,
        'data' => $data
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    // Optional timeout settings
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 

    $response = curl_exec($ch);
    curl_close($ch);

    return $response !== false;
}

function getTokensByUserId($conn, $userId) {
    $stmt = $conn->prepare("SELECT fcm_token FROM users WHERE id = ? AND fcm_token IS NOT NULL AND fcm_token != ''");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $tokens = [];
    while ($row = $res->fetch_assoc()) {
        $tokens[] = $row['fcm_token'];
    }
    $stmt->close();
    return $tokens;
}

function getAdminTokens($conn) {
    $stmt = $conn->prepare("SELECT fcm_token FROM users WHERE role = 'admin' AND fcm_token IS NOT NULL AND fcm_token != ''");
    $stmt->execute();
    $res = $stmt->get_result();
    $tokens = [];
    while ($row = $res->fetch_assoc()) {
        $tokens[] = $row['fcm_token'];
    }
    $stmt->close();
    return $tokens;
}

function getAllCustomerTokens($conn) {
    $stmt = $conn->prepare("SELECT fcm_token FROM users WHERE role = 'customer' AND fcm_token IS NOT NULL AND fcm_token != ''");
    $stmt->execute();
    $res = $stmt->get_result();
    $tokens = [];
    while ($row = $res->fetch_assoc()) {
        $tokens[] = $row['fcm_token'];
    }
    $stmt->close();
    return $tokens;
}
?>
