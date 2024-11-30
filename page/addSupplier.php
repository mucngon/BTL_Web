<?php
// Kết nối cơ sở dữ liệu
require_once 'config.php';
require_once 'database.php';

// Kiểm tra nếu form đã được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplierName = $_POST['SupplierName'];
    $contact = $_POST['Contact'];
    $email = $_POST['Email'];
    $typeOfProduct = $_POST['TypeOfProduct'];
    $establishedDate = $_POST['EstablishedDate'];  // Lấy ngày thành lập từ form

    // Kiểm tra ảnh đã được tải lên chưa
    if (isset($_FILES['Image']) && $_FILES['Image']['error'] == 0) {
        // Lấy thông tin ảnh
        $image = $_FILES['Image'];
        $imageName = strtoupper(substr($supplierName, 0, 1)) . time() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
        $imagePath = '../uploads/' . $imageName;
        
        // Di chuyển ảnh vào thư mục 'uploads'
        if (move_uploaded_file($image['tmp_name'], $imagePath)) {
            // Lưu thông tin vào cơ sở dữ liệu
            try {
                $db = new Database($config['db']);
                $conn = $db->getConnection();

                // Thêm nhà cung cấp vào cơ sở dữ liệu, bao gồm đường dẫn ảnh và ngày thành lập
                $sql = "INSERT INTO Supplier (SupplierName, Contact, Email, TypeOfProduct, ImageURL, EstablishedDate) 
                        VALUES (:SupplierName, :Contact, :Email, :TypeOfProduct, :ImageURL, :EstablishedDate)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':SupplierName', $supplierName);
                $stmt->bindParam(':Contact', $contact);
                $stmt->bindParam(':Email', $email);
                $stmt->bindParam(':TypeOfProduct', $typeOfProduct);
                $stmt->bindParam(':ImageURL', $imagePath);
                $stmt->bindParam(':EstablishedDate', $establishedDate);  // Ràng buộc ngày thành lập
                
                if ($stmt->execute()) {
                    // Chuyển hướng về trang danh sách sau khi thêm thành công
                    header('Location: supplier.php');
                    exit();
                } else {
                    echo "Lỗi khi thêm dữ liệu vào cơ sở dữ liệu.";
                }
            } catch (PDOException $e) {
                echo "Lỗi khi kết nối cơ sở dữ liệu: " . $e->getMessage();
            }
        } else {
            echo "Lỗi khi upload ảnh.";
        }
    } else {
        echo "Ảnh chưa được chọn hoặc có lỗi khi tải lên.";
    }
}
?>