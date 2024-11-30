<?php
include 'db_connection.php'; // Kết nối với cơ sở dữ liệu

$sql = "SELECT SupplierName, Contact, Email, TypeOfProduct, ImageURL FROM Supplier";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Nhà Cung Cấp</title>
</head>
<body>

<h2>Danh sách Nhà Cung Cấp</h2>
<table border="1">
    <tr>
        <th>Tên Nhà Cung Cấp</th>
        <th>Liên Hệ</th>
        <th>Email</th>
        <th>Loại Sản Phẩm</th>
        <th>Ảnh</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        // Xuất dữ liệu của từng hàng
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["SupplierName"] . "</td>";
            echo "<td>" . $row["Contact"] . "</td>";
            echo "<td>" . $row["Email"] . "</td>";
            echo "<td>" . $row["TypeOfProduct"] . "</td>";
            echo "<td><img src='" . $row["ImageURL"] . "' alt='" . $row["SupplierName"] . "' width='100'></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Không có nhà cung cấp nào.</td></tr>";
    }
    $conn->close(); // Đóng kết nối
    ?>
</table>

</body>
</html>
