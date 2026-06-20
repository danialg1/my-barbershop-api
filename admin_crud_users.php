<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

$action = $data['action'] ?? '';

if ($action === 'read') {
    $role = $conn->real_escape_string($data['role'] ?? 'customer');
    $sql = "SELECT id, name, email, phone, role, is_active, photo FROM users WHERE role = '$role' ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $users = [];
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    echo json_encode(["status" => "success", "data" => $users]);
} 
else if ($action === 'create') {
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone'] ?? '');
    $role = $conn->real_escape_string($data['role'] ?? 'customer');
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check && $check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email sudah digunakan"]);
        exit;
    }
    
    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES ('$name', '$email', '$password', '$phone', '$role')";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
else if ($action === 'update') {
    $id = (int)$data['id'];
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone'] ?? '');
    
    // Check if email exists for other users
    $check = $conn->query("SELECT id FROM users WHERE email='$email' AND id != $id");
    if ($check && $check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email sudah digunakan"]);
        exit;
    }
    
    $pwd_query = "";
    if (!empty($data['password'])) {
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $pwd_query = ", password='$password'";
    }
    
    $sql = "UPDATE users SET name='$name', email='$email', phone='$phone' $pwd_query WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
else if ($action === 'delete') {
    $id = (int)$data['id'];
    $sql = "DELETE FROM users WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menghapus pengguna. Pastikan tidak ada transaksi yang terikat."]);
    }
}
else {
    echo json_encode(["status" => "error", "message" => "Aksi tidak valid"]);
}
?>
