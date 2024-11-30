<?php
session_start();  // Khởi tạo session

require 'database.php';

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

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Đăng nhập thành công, tạo session cho người dùng
            $_SESSION['user_id'] = $user['id'];  // Lưu ID người dùng vào session
            $_SESSION['username'] = $user['username'];  // Lưu tên người dùng vào session

            // Chuyển hướng đến trang chính
            header("Location: index.php");
            exit();
        } else {
            echo "Tên đăng nhập hoặc mật khẩu không đúng.";
        }
    } catch (PDOException $e) {
        echo "Lỗi khi đăng nhập: " . $e->getMessage();
    }
}
?>
