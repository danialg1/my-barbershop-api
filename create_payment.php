<?php
require 'koneksi.php';
require 'config.php';
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'])) {
    $user_id = $conn->real_escape_string($data['user_id']);
    $service_id = $conn->real_escape_string($data['service_id']);
    $res_date = $conn->real_escape_string($data['reservation_date']); 
    // Ambil harga layanan asli
    $sql_price = "SELECT price FROM services WHERE id='$service_id'";
    $res_price = $conn->query($sql_price);
    $gross_amount = 20000; // Default fallback
    if ($res_price && $res_price->num_rows > 0) {
        $gross_amount = (int)$res_price->fetch_assoc()['price'];
    }

    $use_discount = isset($data['use_discount']) && $data['use_discount'] == true;
    $discount_applied = 0;

    if ($use_discount) {
        // Cek Poin Elite User
        $sql_points = "SELECT SUM(s.price) as total_spent FROM reservations r JOIN services s ON r.service_id = s.id WHERE r.user_id='$user_id' AND r.status IN ('completed', 'confirmed')";
        $res_points = $conn->query($sql_points);
        $total_spent = ($res_points && $res_points->num_rows > 0) ? ($res_points->fetch_assoc()['total_spent'] ?? 0) : 0;
        
        $sql_user = "SELECT used_elite_points FROM users WHERE id='$user_id'";
        $res_user = $conn->query($sql_user);
        $used_points = ($res_user && $res_user->num_rows > 0) ? (int)$res_user->fetch_assoc()['used_elite_points'] : 0;
        
        $earned_points = floor($total_spent / 10000);
        $current_elite_points = max(0, $earned_points - $used_points);
        
        if ($current_elite_points >= 25) {
            $gross_amount = $gross_amount / 2; // Diskon 50%
            $discount_applied = 1;
            
            // Potong poin
            $conn->query("UPDATE users SET used_elite_points = used_elite_points + 25 WHERE id='$user_id'");
        }
    }

    $barber_id = $conn->real_escape_string($data['barber_id']);

    // 1. Simpan ke database dengan status 'pending'
    $sql = "INSERT INTO reservations (user_id, barber_id, service_id, reservation_date, status, discount_applied) 
            VALUES ('$user_id', '$barber_id', '$service_id', '$res_date', 'pending', '$discount_applied')";
            
    if ($conn->query($sql) === TRUE) {
        $db_id = $conn->insert_id; 
        $order_id = "ORDER-" . $db_id . "-" . time(); 

        // 2. Siapkan Payload Midtrans
        $serverKey = MIDTRANS_SERVER_KEY;
        $payload = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $gross_amount,
            ],
            'callbacks' => [
                'finish' => 'https://example.com/success'
            ]
        ];

        // 3. Tembak API Midtrans
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://app.sandbox.midtrans.com/snap/v1/transactions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':')
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        // --- INI BAGIAN YANG KITA PERBAIKI ---
        // Bongkar jawaban asli dari Midtrans
        $midtrans_data = json_decode($response, true);
        
        // Bungkus ulang agar sesuai dengan selera Flutter
        if (isset($midtrans_data['redirect_url'])) {
            echo json_encode([
                "status" => "success",
                "payment_url" => $midtrans_data['redirect_url']
            ]);
        } else {
            // Kalau misal Server Key salah atau API Midtrans error
            echo json_encode([
                "status" => "error", 
                "message" => "Ditolak Midtrans: " . json_encode($midtrans_data)
            ]);
        }
        // -------------------------------------

    } else {
        echo json_encode(["status" => "error", "message" => "Gagal simpan ke database"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data user tidak lengkap"]);
}
?>