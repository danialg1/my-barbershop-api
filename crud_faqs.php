<?php
header('Content-Type: application/json');
require 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

if ($action === 'read') {
    $result = $conn->query("SELECT * FROM faqs ORDER BY id ASC");
    $faqs = [];
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $faqs]);
} elseif ($action === 'create') {
    $question = $conn->real_escape_string($data['question'] ?? '');
    $answer = $conn->real_escape_string($data['answer'] ?? '');
    if (!empty($question) && !empty($answer)) {
        if ($conn->query("INSERT INTO faqs (question, answer) VALUES ('$question', '$answer')")) {
            echo json_encode(['status' => 'success', 'message' => 'FAQ berhasil ditambahkan']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan FAQ']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Pertanyaan dan jawaban harus diisi']);
    }
} elseif ($action === 'update') {
    $id = (int)($data['id'] ?? 0);
    $question = $conn->real_escape_string($data['question'] ?? '');
    $answer = $conn->real_escape_string($data['answer'] ?? '');
    if ($id > 0 && !empty($question) && !empty($answer)) {
        if ($conn->query("UPDATE faqs SET question='$question', answer='$answer' WHERE id=$id")) {
            echo json_encode(['status' => 'success', 'message' => 'FAQ berhasil diperbarui']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui FAQ']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    }
} elseif ($action === 'delete') {
    $id = (int)($data['id'] ?? 0);
    if ($id > 0) {
        if ($conn->query("DELETE FROM faqs WHERE id=$id")) {
            echo json_encode(['status' => 'success', 'message' => 'FAQ berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus FAQ']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
}
?>
