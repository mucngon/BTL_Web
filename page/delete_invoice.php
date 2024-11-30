<?php
if (isset($_GET['id'])) {
    $invoice_id = $_GET['id'];

    // Kết nối cơ sở dữ liệu
    $connection = new mysqli("localhost", "root", "1234", "supplierdatabase");

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Xóa hóa đơn
    $sql = "DELETE FROM invoice WHERE InvoiceID=?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $invoice_id);

    if ($stmt->execute()) {
        echo "Invoice deleted successfully!";
        header('Location: invoice.php'); // Quay lại trang danh sách
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
}
?>
