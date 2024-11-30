<?php
// Lấy id từ tham số GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Kết nối cơ sở dữ liệu
        require_once 'config.php';
        require_once 'database.php';
        $db = new Database($config['db']);
        $conn = $db->getConnection();

        // Thực hiện xóa
        $sql = "DELETE FROM Supplier WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Chuyển hướng về trang trước sau khi xóa
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (PDOException $e) {
        echo "Lỗi khi xóa dữ liệu: " . $e->getMessage();
    }
}
?>
