<?php
// Require các file cần thiết
require_once 'config.php';
require_once 'database.php';

// Khởi tạo kết nối database
$db = new Database($config['db']);
$conn = $db->getConnection();

// Thiết lập các thông số phân trang
$records_per_page = $config['pagination']['records_per_page'];
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
try {
    // Đếm tổng số bản ghi
    $total_sql = "SELECT COUNT(*) FROM Supplier";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute();
    $total_records = $total_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // Truy vấn lấy dữ liệu có giới hạn
    $sql = "SELECT * FROM Supplier LIMIT :offset, :records_per_page";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
    exit();
}

// Hàm tạo URL phân trang an toàn
function getPaginationUrl($page)
{
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Supplier List - Business Frontpage</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="../css/styles.css" rel="stylesheet" />

</head>

<body>
    <!-- Responsive navbar-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container px-5">
            <a class="navbar-brand" href="index.php">
                <img src="../uploads/logo.jfif" alt="Your Logo" style="height: 40px; margin-right: 10px;">
                LuxLux
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page"
                            href="supplier.php">Suppliers</a></li>
                    <li class="nav-item"><a class="nav-link" href="invoice.php">Invoice</a></li>
                    <li class="nav-item"><a class="nav-link" href="statistic.php">Statistic</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container mt-5">
        <h1 class="mb-4">Supplier List</h1>
        <!-- Buttons for Add, Edit, Delete -->
        <div class="mb-3">
            <button class="btn btn-success" onclick="window.location.href='add_supplier.html'">Add Supplier</button>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Type of Product</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="supplierTable">
                <?php if (!empty($result)): ?>
                    <?php foreach ($result as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                            <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            <td><?php echo htmlspecialchars($row['TypeOfProduct']); ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm delete"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="text-white">Delete</a>
                                </button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Không có dữ liệu</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Hiển thị thông tin phân trang -->
        <div class="page-info-container">
            <i class="fas fa-list"></i>
            <p class="page-info-text">
                Đang hiển thị
                <span class="record-numbers">
                    <?php echo number_format($offset + 1); ?> -
                    <?php echo number_format(min($offset + $records_per_page, $total_records)); ?>
                </span>
                của
                <span class="total-records">
                    <?php echo number_format($total_records); ?>
                </span>
                bản ghi
            </p>
        </div>

        <!-- Thanh phân trang -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <!-- Đầu trang và Trang trước -->
                <?php if ($page > 1): ?>
                    <a href="<?php echo getPaginationUrl(1); ?>" class="pagination-link" aria-label="Đầu trang">&laquo; Đầu</a>
                    <a href="<?php echo getPaginationUrl($page - 1); ?>" class="pagination-link"
                        aria-label="Trang trước">&lsaquo; Trước</a>
                <?php endif; ?>

                <?php
                // Hiển thị các số trang
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $page) {
                        echo "<a class='pagination-link active' href='" . getPaginationUrl($i) . "'>$i</a>";
                    } else {
                        echo "<a class='pagination-link' href='" . getPaginationUrl($i) . "'>$i</a>";
                    }
                }
                ?>

                <!-- Trang sau và Cuối trang -->
                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo getPaginationUrl($page + 1); ?>" class="pagination-link" aria-label="Trang sau">Sau
                        &rsaquo;</a>
                    <a href="<?php echo getPaginationUrl($total_pages); ?>" class="pagination-link" aria-label="Cuối trang">Cuối
                        &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>



</body>

</html>