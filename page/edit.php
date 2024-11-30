<?php
// Require các file cần thiết
require_once 'config.php';
require_once 'database.php';

$db = new Database($config['db']);
$conn = $db->getConnection();

// Kiểm tra ID có được truyền vào không
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Truy vấn lấy dữ liệu của nhà cung cấp từ ID
    $sql = "SELECT * FROM Supplier WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$supplier) {
        echo "Không tìm thấy nhà cung cấp với ID này!";
        exit();
    }

    // Kiểm tra xem form có được gửi không
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Lấy dữ liệu từ form
        $name = $_POST['SupplierName'];
        $contact = $_POST['Contact'];
        $email = $_POST['Email'];
        $productType = $_POST['TypeOfProduct'];
        $establishedDate = $_POST['EstablishedDate'];  // Ngày thành lập

        // Kiểm tra nếu có ảnh mới được tải lên
        if (isset($_FILES['Image']) && $_FILES['Image']['error'] == 0) {
            $image = $_FILES['Image'];
            $imageName = strtoupper(substr($name, 0, 1)) . time() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
            $imagePath = '../uploads/' . $imageName;

            // Di chuyển ảnh mới vào thư mục 'uploads'
            if (move_uploaded_file($image['tmp_name'], $imagePath)) {
                // Cập nhật thông tin nhà cung cấp vào database bao gồm ảnh mới
                $update_sql = "UPDATE Supplier SET SupplierName = :name, Contact = :contact, Email = :email, TypeOfProduct = :productType, EstablishedDate = :establishedDate, ImageURL = :imageURL WHERE id = :id";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bindValue(':imageURL', $imagePath);
            } else {
                echo "Lỗi khi upload ảnh!";
                exit();
            }
        } else {
            // Cập nhật thông tin nhà cung cấp vào database, giữ nguyên ảnh cũ nếu không có ảnh mới
            $update_sql = "UPDATE Supplier SET SupplierName = :name, Contact = :contact, Email = :email, TypeOfProduct = :productType, EstablishedDate = :establishedDate WHERE id = :id";
            $update_stmt = $conn->prepare($update_sql);
        }

        // Gán các giá trị còn lại cho câu lệnh SQL
        $update_stmt->bindValue(':name', $name);
        $update_stmt->bindValue(':contact', $contact);
        $update_stmt->bindValue(':email', $email);
        $update_stmt->bindValue(':productType', $productType);
        $update_stmt->bindValue(':establishedDate', $establishedDate);  // Ngày thành lập
        $update_stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            header('Location: supplier.php'); // Sau khi chỉnh sửa, chuyển về trang danh sách
            exit();
        } else {
            echo "Lỗi khi cập nhật thông tin!";
        }
    }
} else {
    echo "ID không hợp lệ!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Edit Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Supplier</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="SupplierName" class="form-label">Supplier Name</label>
                <input type="text" class="form-control" id="SupplierName" name="SupplierName" value="<?php echo htmlspecialchars($supplier['SupplierName']); ?>" required />
            </div>
            <div class="mb-3">
                <label for="Contact" class="form-label">Contact</label>
                <input type="text" class="form-control" id="Contact" name="Contact" value="<?php echo htmlspecialchars($supplier['Contact']); ?>" required />
            </div>
            <div class="mb-3">
                <label for="Email" class="form-label">Email</label>
                <input type="email" class="form-control" id="Email" name="Email" value="<?php echo htmlspecialchars($supplier['Email']); ?>" required />
            </div>
            <div class="mb-3">
                <label for="TypeOfProduct" class="form-label">Type of Product</label>
                <input type="text" class="form-control" id="TypeOfProduct" name="TypeOfProduct" value="<?php echo htmlspecialchars($supplier['TypeOfProduct']); ?>" required />
            </div>
            <div class="mb-3">
                <label for="EstablishedDate" class="form-label">Established Date</label>
                <input type="date" class="form-control" id="EstablishedDate" name="EstablishedDate" value="<?php echo htmlspecialchars($supplier['EstablishedDate']); ?>" required />
            </div>
            <div class="mb-3">
                <label for="Image" class="form-label">Image</label>
                <input type="file" class="form-control" id="Image" name="Image" accept="image/*" />
                <?php if ($supplier['ImageURL']): ?>
                    <p>Current Image: <img src="<?php echo htmlspecialchars($supplier['ImageURL']); ?>" alt="Supplier Image" style="max-width: 100px;"></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Supplier</button>
        </form>
    </div>
</body>
</html>
