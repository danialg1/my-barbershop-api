<?php
$conn = new mysqli('localhost', 'root', '', 'db_barbershop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("ALTER TABLE users ADD COLUMN fcm_token VARCHAR(255) NULL");
echo "Column fcm_token added successfully\n";
?>
