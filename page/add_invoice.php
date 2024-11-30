<?php
// Kiểm tra xem form có được gửi hay không
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kết nối đến cơ sở dữ liệu
    $connection = new mysqli("localhost", "root", "1234", "supplierdatabase");

    // Kiểm tra kết nối
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Lấy dữ liệu từ form
    $supplier_id = $_POST['supplier_id'];
    $customer_name = $_POST['customer_name'];
    $invoice_date = $_POST['invoice_date'];
    $total_amount = $_POST['total_amount'];

    // Chuẩn bị câu lệnh SQL để chèn dữ liệu vào bảng invoices
    $sql = "INSERT INTO invoice (SupplierID, CustomerName, InvoiceDate, TotalAmount) 
            VALUES (?, ?, ?, ?)";

    // Sử dụng prepared statement để bảo vệ khỏi SQL injection
    if ($stmt = $connection->prepare($sql)) {
        // Liên kết các tham số với câu lệnh SQL
        $stmt->bind_param("issd", $supplier_id, $customer_name, $invoice_date, $total_amount);

        // Thực thi câu lệnh SQL
        if ($stmt->execute()) {
            echo "Invoice added successfully!";
            header('Location: invoice.php');
        } else {
            echo "Error: " . $stmt->error;
        }

        // Đóng statement
        $stmt->close();
    } else {
        echo "Error preparing the statement: " . $connection->error;
    }

    // Đóng kết nối
    $connection->close();
}
?>
