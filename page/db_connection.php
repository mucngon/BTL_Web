<?php
$servername = "localhost";
$username = "root";
$password = "1234"; // điền mật khẩu MySQL của bạn vào đây
$dbname = "SupplierDatabase";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
