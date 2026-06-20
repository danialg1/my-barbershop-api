<?php
require 'koneksi.php';
$data = json_decode(file_get_contents("php://input"), true);

$action = $data['action'] ?? '';

function processImageUpload($base64_string) {
    if (empty($base64_string)) return null;
    
    $target_dir = "uploads/services/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Check if there is a prefix like data:image/png;base64,
    if (strpos($base64_string, 'base64,') !== false) {
        $parts = explode('base64,', $base64_string);
        $image_data = base64_decode($parts[1]);
    } else {
        $image_data = base64_decode($base64_string);
    }
    
    if ($image_data === false) return null;
    
    $f = finfo_open();
    $mime_type = finfo_buffer($f, $image_data, FILEINFO_MIME_TYPE);
    finfo_close($f);
    
    $extension = 'png';
    if ($mime_type == 'image/jpeg') $extension = 'jpg';
    if ($mime_type == 'image/png') $extension = 'png';
    if ($mime_type == 'image/webp') $extension = 'webp';
    
    $file_name = uniqid() . '.' . $extension;
    $file_path = $target_dir . $file_name;
    
    if (file_put_contents($file_path, $image_data)) {
        return $file_name;
    }
    return null;
}

if ($action === 'read') {
    $sql = "SELECT * FROM services ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $services = [];
    $base_url = "http://192.168.1.4/barbershop_api/uploads/services/";
    
    if ($result) {
        while($row = $result->fetch_assoc()) {
            if (!empty($row['image'])) {
                // If it's already a full URL (just in case), use it. Otherwise prepend base_url
                if (strpos($row['image'], 'http') === 0) {
                    $row['image_url'] = $row['image'];
                } else {
                    $row['image_url'] = $base_url . $row['image'];
                }
            } else {
                $row['image_url'] = $base_url . 'default_service.png';
            }
            $services[] = $row;
        }
    }
    echo json_encode(["status" => "success", "data" => $services]);
} 
else if ($action === 'create') {
    $name = $conn->real_escape_string($data['name']);
    $desc = $conn->real_escape_string($data['description'] ?? '');
    $price = (int)$data['price'];
    
    $image_name = 'default_service.png';
    if (isset($data['image_base64']) && !empty($data['image_base64'])) {
        $uploaded = processImageUpload($data['image_base64']);
        if ($uploaded) {
            $image_name = $uploaded;
        }
    }
    
    $sql = "INSERT INTO services (name, description, price, image) VALUES ('$name', '$desc', $price, '$image_name')";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
else if ($action === 'update') {
    $id = (int)$data['id'];
    $name = $conn->real_escape_string($data['name']);
    $desc = $conn->real_escape_string($data['description'] ?? '');
    $price = (int)$data['price'];
    
    $image_sql = "";
    if (isset($data['image_base64']) && !empty($data['image_base64'])) {
        $uploaded = processImageUpload($data['image_base64']);
        if ($uploaded) {
            // Optional: delete old image
            $old_q = $conn->query("SELECT image FROM services WHERE id=$id");
            if ($old_q && $old_q->num_rows > 0) {
                $old_row = $old_q->fetch_assoc();
                $old_img = $old_row['image'];
                if ($old_img && $old_img !== 'default_service.png' && file_exists("uploads/services/" . $old_img)) {
                    unlink("uploads/services/" . $old_img);
                }
            }
            $image_sql = ", image='$uploaded'";
        }
    }
    
    $sql = "UPDATE services SET name='$name', description='$desc', price=$price $image_sql WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
else if ($action === 'delete') {
    $id = (int)$data['id'];
    
    // Delete image file before deleting record
    $old_q = $conn->query("SELECT image FROM services WHERE id=$id");
    if ($old_q && $old_q->num_rows > 0) {
        $old_row = $old_q->fetch_assoc();
        $old_img = $old_row['image'];
        if ($old_img && $old_img !== 'default_service.png' && file_exists("uploads/services/" . $old_img)) {
            unlink("uploads/services/" . $old_img);
        }
    }
    
    $sql = "DELETE FROM services WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menghapus layanan. Pastikan layanan tidak terikat dengan transaksi."]);
    }
}
else {
    echo json_encode(["status" => "error", "message" => "Aksi tidak valid"]);
}
?>
