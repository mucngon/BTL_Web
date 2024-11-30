<?php
session_start();
// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
// Kết nối database
$config = [
    'db' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '1234',
        'dbname' => 'supplierdatabase'
    ],
    'pagination' => [
        'records_per_page' => 10
    ]
];

$conn = mysqli_connect(
    $config['db']['host'],
    $config['db']['username'],
    $config['db']['password'],
    $config['db']['dbname']
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? $_GET['search'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$supplierFilter = isset($_GET['supplier']) ? $_GET['supplier'] : '';

// Xử lý phân trang
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = $config['pagination']['records_per_page'];
$offset = ($page - 1) * $limit;

// Tạo câu truy vấn với điều kiện tìm kiếm
$whereClause = "WHERE 1=1";
if (!empty($search)) {
    $whereClause .= " AND (CustomerName LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'
                    OR i.InvoiceID LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
if (!empty($dateFrom)) {
    $whereClause .= " AND InvoiceDate >= '" . mysqli_real_escape_string($conn, $dateFrom) . "'";
}
if (!empty($dateTo)) {
    $whereClause .= " AND InvoiceDate <= '" . mysqli_real_escape_string($conn, $dateTo) . "'";
}
if (!empty($supplierFilter)) {
    $whereClause .= " AND i.SupplierID = " . mysqli_real_escape_string($conn, $supplierFilter);
}

// Đếm tổng số record để phân trang
$countQuery = "SELECT COUNT(*) as total FROM Invoice i $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);

// Lấy danh sách hóa đơn
$query = "SELECT i.InvoiceID, s.SupplierName, i.CustomerName, 
          i.InvoiceDate, i.TotalAmount, i.SupplierID
          FROM Invoice i
          JOIN supplier s ON i.SupplierID = s.id
          $whereClause
          ORDER BY i.InvoiceID DESC
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Lấy danh sách nhà cung cấp cho filter
$supplierQuery = "SELECT id, SupplierName FROM supplier ORDER BY SupplierName";
$suppliers = mysqli_query($conn, $supplierQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Invoice Management</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</head>

<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container px-5">
            <a class="navbar-brand" href="index.php">
                <img src="../uploads/logo.jfif" alt="Your Logo" style="height: 40px; margin-right: 10px;">
                LuxLux
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="supplier.php">Suppliers</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#!">Invoice</a></li>
                    <li class="nav-item"><a class="nav-link" href="statistic.php">Statistic</a></li>
                    <li class="nav-item"><a class="nav-link" href="Logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container px-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Invoice Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                <i class="bi bi-plus-circle"></i> New Invoice
            </button>
        </div>

        <!-- Search and Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Search by ID or Customer"
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="supplier">
                            <option value="">All Suppliers</option>
                            <?php while ($supplier = mysqli_fetch_assoc($suppliers)): ?>
                                <option value="<?php echo $supplier['id']; ?>" <?php echo $supplierFilter == $supplier['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supplier['SupplierName']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoice Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Supplier Name</th>
                        <th>Customer Name</th>
                        <th>Invoice Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['InvoiceID']; ?></td>
                            <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                            <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['InvoiceDate'])); ?></td>
                            <td><?php echo number_format($row['TotalAmount'], 2); ?></td>
                            <td>
                                <!-- Sửa hóa đơn -->


                                <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $row['InvoiceID']; ?>"
                                    data-supplier="<?php echo $row['SupplierID']; ?>"
                                    data-customer="<?php echo htmlspecialchars($row['CustomerName']); ?>"
                                    data-date="<?php echo $row['InvoiceDate']; ?>"
                                    data-amount="<?php echo $row['TotalAmount']; ?>" data-bs-toggle="modal"
                                    data-bs-target="#editInvoiceModal">
                                    <i class="bi bi-pencil"></i>
                                </button>



                                <!-- Xóa hóa đơn -->
                                <a href="delete_invoice.php?id=<?php echo $row['InvoiceID']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this invoice?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>&supplier=<?php echo $supplierFilter; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>&supplier=<?php echo $supplierFilter; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>&supplier=<?php echo $supplierFilter; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Add Invoice Modal -->
    <div class="modal fade" id="addInvoiceModal" tabindex="-1" aria-labelledby="addInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_invoice.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addInvoiceModalLabel">New Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Invoice Fields -->
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" name="supplier_id" id="supplier_id" required>
                                <?php
                                // Fetch suppliers from the database
                                $connection = new mysqli("localhost", "root", "1234", "supplierdatabase");
                                $result = $connection->query("SELECT id, SupplierName FROM supplier");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['SupplierName']}</option>";
                                }
                                $connection->close();
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="customer_name" id="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="invoice_date" class="form-label">Invoice Date</label>
                            <input type="date" class="form-control" name="invoice_date" id="invoice_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="total_amount" class="form-label">Total Amount</label>
                            <input type="number" step="0.01" class="form-control" name="total_amount" id="total_amount"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Sửa Hóa Đơn -->

    <!-- Modal Edit Invoice -->
    <!-- Modal for Edit Invoice -->
    <div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="edit_invoice.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editInvoiceModalLabel">Edit Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Invoice Fields -->
                        <input type="hidden" name="InvoiceID" id="invoiceID"> <!-- Hidden input for InvoiceID -->
                        <div class="mb-3">
                            <label for="supplierID" class="form-label">Supplier</label>
                            <select name="SupplierID" id="supplierID" class="form-control" required>

                                <?php
                                // Fetch suppliers from the database
                                $connection = new mysqli("localhost", "root", "1234", "supplierdatabase");
                                $result = $connection->query("SELECT id, SupplierName FROM supplier");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['SupplierName']}</option>";
                                }
                                $connection->close();
                                ?>

                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="CustomerName" id="customerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="invoiceDate" class="form-label">Invoice Date</label>
                            <input type="date" class="form-control" name="InvoiceDate" id="invoiceDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="totalAmount" class="form-label">Total Amount</label>
                            <input type="number" class="form-control" name="TotalAmount" id="totalAmount" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Lắng nghe sự kiện khi modal mở ra
        document.addEventListener('DOMContentLoaded', function () {
            const editButtons = document.querySelectorAll('.edit-btn');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Lấy các giá trị từ data attributes
                    const invoiceID = this.getAttribute('data-id');
                    const supplierID = this.getAttribute('data-supplier');
                    const customerName = this.getAttribute('data-customer');
                    const invoiceDate = this.getAttribute('data-date');
                    const totalAmount = this.getAttribute('data-amount');

                    // Điền dữ liệu vào form trong modal
                    document.getElementById('invoiceID').value = invoiceID;
                    document.getElementById('customerName').value = customerName;
                    document.getElementById('invoiceDate').value = invoiceDate;
                    document.getElementById('totalAmount').value = totalAmount;

                    // Chọn supplierID tương ứng trong dropdown
                    document.getElementById('supplierID').value = supplierID;
                });
            });
        });
    </script>


    <!-- Hiển thị danh sách hóa đơn -->



    <!-- Modal Xóa Hóa Đơn -->
    <div class="modal fade" id="deleteInvoiceModal" tabindex="-1" aria-labelledby="deleteInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteInvoiceModalLabel">Delete Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this invoice?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="delete_invoice.php" id="deleteInvoiceBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>