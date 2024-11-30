<?php
// register_process.php

require_once 'database.php';



$config = [
    'host' => 'localhost',
    'dbname' => 'supplierdatabase',  // Thay 'your_database' bằng tên CSDL của bạn
    'username' => 'root',
    'password' => '1234'
];

$database = new Database($config);
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        echo "Mật khẩu không trùng khớp!";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            echo "Đăng ký thành công!";
            header("Location: auth.php");
            exit();
        }
    } catch (PDOException $e) {
        echo "Lỗi khi đăng ký: " . $e->getMessage();
    }
}
?>
