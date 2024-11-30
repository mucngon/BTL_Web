<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invoice_id = $_POST['InvoiceID'];  // ID của hóa đơn
    $supplier_id = $_POST['SupplierID'];  // ID của nhà cung cấp
    $customer_name = $_POST['CustomerName'];  // Tên khách hàng
    $invoice_date = $_POST['InvoiceDate'];  // Ngày hóa đơn
    $total_amount = $_POST['TotalAmount'];  // Tổng số tiền

    // Kết nối cơ sở dữ liệu
    $connection = new mysqli("localhost", "root", "1234", "supplierdatabase");

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Câu lệnh SQL để cập nhật hóa đơn
    $sql = "UPDATE invoice SET SupplierID=?, CustomerName=?, InvoiceDate=?, TotalAmount=? WHERE InvoiceID=?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("issdi", $supplier_id, $customer_name, $invoice_date, $total_amount, $invoice_id);

    if ($stmt->execute()) {
        echo "Invoice updated successfully!";
        header("Location: invoice_list.php");  // Quay lại trang danh sách hóa đơn
    } else {
        echo "Error updating invoice: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
}
?>
