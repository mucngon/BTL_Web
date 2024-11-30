<?php
session_start();
$host = 'localhost';
$db = 'supplierdatabase';
$user = 'root';
$pass = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Truy vấn thông tin user và supplier
    $sql = "SELECT u.*, s.SupplierName, s.Contact, s.Email as SupplierEmail, 
                   s.TypeOfProduct, s.ImageURL, s.EstablishedDate
            FROM users u
            LEFT JOIN supplier s ON u.supplier_id = s.id
            WHERE u.id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug thông tin nếu cần
    /*
    echo "<pre>";
    print_r($user_data);
    echo "</pre>";
    */

} catch (PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
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
        <a class="navbar-brand" href="#!">
                <img src="../uploads/logo.jfif" alt="Your Logo" style="height: 40px; margin-right: 10px;">
                LuxLux
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h3>User Information</h3>
                    </div>
                    <div class="card-body">
                        <!-- Hiển thị thông tin người dùng -->
                        <div class="row mb-3">
                            <div class="col-md-4">Username:</div>
                            <div class="col-md-8"><?php echo htmlspecialchars($user_data['username']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">Role:</div>
                            <div class="col-md-8"><?php echo htmlspecialchars($user_data['role']); ?></div>
                        </div>

                        <?php if ($user_data['supplier_id']): ?>
                            <h4 class="mt-4">Supplier Information</h4>
                            <div class="row mb-3">
                                <div class="col-md-4">Supplier Name:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user_data['SupplierName']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">Contact:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user_data['Contact']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">Email:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user_data['SupplierEmail']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">Type of Product:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user_data['TypeOfProduct']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">Established Date:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($user_data['EstablishedDate']); ?></div>
                            </div>
                            <?php if ($user_data['ImageURL']): ?>
                            <div class="row mb-3">
                                <div class="col-md-4">Image:</div>
                                <div class="col-md-8">
                                    <img src="<?php echo htmlspecialchars($user_data['ImageURL']); ?>" alt="Supplier Image" class="img-fluid" style="max-width: 200px;">
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                This user is not linked to any supplier.
                            </div>
                        <?php endif; ?>

                        <!-- Nút Edit Profile -->
                        <?php if ($user_data['supplier_id']): ?>
                            <div class="text-end mt-4">
                                <a href="edit.php?id=<?php echo htmlspecialchars($user_data['supplier_id']); ?>" class="btn btn-primary">Edit Profile</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>