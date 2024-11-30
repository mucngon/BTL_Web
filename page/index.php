<?php
session_start();  // Khởi tạo session ở đầu file
// Kiểm tra đăng nhập và quyền admin
$host = 'localhost';
$db = 'supplierdatabase';
$user = 'root';
$pass = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Debug thông tin đầu vào
        echo "Username: " . $username . "<br>";

        // Truy vấn lấy thông tin người dùng
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debug thông tin user
        echo "<pre>";
        print_r($user);
        echo "</pre>";

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            // Debug session sau khi set
            echo "Session after login: <pre>";
            print_r($_SESSION);
            echo "</pre>";

            header("Location: index.php");
            exit();
        } else {
            echo "Sai tên đăng nhập hoặc mật khẩu.";
            // Debug thêm thông tin
            echo "Password verify result: " . (password_verify($password, $user['password']) ? 'true' : 'false');
        }
    }
} catch (PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
}


?>
<?php
// Kết nối cơ sở dữ liệu
$host = 'localhost';
$db = 'supplierdatabase'; // Thay bằng tên cơ sở dữ liệu của bạn
$user = 'root'; // Thay bằng tên người dùng của bạn
$pass = '1234'; // Thay bằng mật khẩu của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Câu lệnh SQL để lấy 3 nhà cung cấp có doanh thu lớn nhất
    $stmt = $pdo->prepare("
        SELECT 
            s.id AS SupplierID,
            s.SupplierName,
            s.Contact,
            s.Email,
            s.TypeOfProduct,
            s.ImageURL,
            COUNT(i.InvoiceID) AS OrderCount,
            SUM(i.TotalAmount) AS Revenue
        FROM 
            supplier s
        JOIN 
            Invoice i ON s.id = i.SupplierID
        GROUP BY 
            s.id
        ORDER BY 
            Revenue DESC
        LIMIT 3;
    ");
    $stmt->execute();

    // Lấy kết quả truy vấn
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Business Frontpage - Start Bootstrap Template</title>
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
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>

                    <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="supplier.php">Suppliers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="invoice.php">Invoice</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="statistic.php">Statistic</a>
                            </li>
                        <?php elseif ($_SESSION['user_role'] === 'user'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">Profile</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header-->
    <header class="bg-dark py-5">
        <div class="container px-5">
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center my-5">
                        <h1 class="display-5 fw-bolder text-white mb-2">Manage Your Suppliers More Efficiently</h1>
                        <p class="lead text-white-50 mb-4">Quickly design and customize your own supplier management
                            platform with ease, using the tools and features you need for success!</p>
                        <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                            <a class="btn btn-primary btn-lg px-4 me-sm-3" href="#features">Get Started</a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Features section-->
    <section class="py-5 border-bottom" id="features">
        <div class="container px-5 my-5">
            <div class="row gx-5">
                <!-- Reliability Feature -->
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h2 class="h4 fw-bolder">Reliability</h2>
                    <p>Our suppliers are known for their consistent and dependable service, ensuring timely delivery and
                        high-quality products every time.</p>
                    <a class="text-decoration-none" href="#!">


                    </a>
                </div>
                <!-- Customer-Friendly Feature -->
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3">
                        <i class="bi bi-emoji-smile"></i>
                    </div>
                    <h2 class="h4 fw-bolder">Customer-Friendly</h2>
                    <p>Our suppliers prioritize customer satisfaction, offering easy returns, friendly support, and
                        tailored solutions to meet client needs.</p>
                    <a class="text-decoration-none" href="#!">


                    </a>
                </div>
                <!-- Sustainability Feature -->
                <div class="col-lg-4">
                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3">
                        <i class="bi bi-globe"></i>
                    </div>
                    <h2 class="h4 fw-bolder">Sustainability</h2>
                    <p>Committed to eco-friendly practices, our suppliers use sustainable resources and minimize
                        environmental impact in their processes.</p>
                    <a class="text-decoration-none" href="#!">


                    </a>
                </div>
            </div>
        </div>
    </section>
    <div class="container mt-5">
        <?php
        require_once 'config.php';
        require_once 'database.php';
        require_once 'BusinessBirthdayManager.php'; // File chứa code chúc mừng sinh nhật
        
        try {
            $db = new Database($config['db']);
            $conn = $db->getConnection();
            $birthdayManager = new BusinessBirthdayManager($conn);
            echo $birthdayManager->displayBirthdayMessages();
        } catch (Exception $e) {
            echo '<div class="alert alert-danger" role="alert">';
            echo 'Đã xảy ra lỗi: ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
    </div>
    <!-- Pricing section-->
    <section class="bg-light py-5 border-bottom">
        <div class="container px-5 my-5">
            <div class="text-center mb-5">
                <h2 class="fw-bolder">Top 3 suppliers</h2>
                <p class="lead mb-0">Unmatched quality, unbeatable revenue</p>
            </div>
            <div class="row gx-5 justify-content-center">
                <!-- Pricing card free-->

                <div class="container my-5">
                    <div class="row">
                        <?php foreach ($suppliers as $supplier): ?>
                            <!-- Supplier Card -->
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?= htmlspecialchars($supplier['ImageURL']) ?>" class="card-img-top"
                                        alt="<?= htmlspecialchars($supplier['SupplierName']) ?>"
                                        style="height: 200px; object-fit: cover;">
                                    <div class="card-body p-4">
                                        <h5 class="card-title"><?= htmlspecialchars($supplier['SupplierName']) ?></h5>
                                        <div class="mb-3">
                                            <span
                                                class="display-4 fw-bold">$<?= number_format($supplier['Revenue'], 2) ?></span>
                                            <span class="text-muted">Revenue</span>
                                        </div>
                                        <ul class="list-unstyled mb-4">
                                            <li class="mb-2">
                                                <i class="bi bi-check text-primary"></i>
                                                <strong><?= $supplier['OrderCount'] ?> orders</strong>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-check text-primary"></i>
                                                Product Type: <?= htmlspecialchars($supplier['TypeOfProduct']) ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-check text-primary"></i>
                                                Contact: <?= htmlspecialchars($supplier['Contact']) ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-check text-primary"></i>
                                                Email: <?= htmlspecialchars($supplier['Email']) ?>
                                            </li>
                                        </ul>
                                        <div class="d-grid"><a class="btn btn-outline-primary" href="#!"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

            </div>
        </div>
        </div>
    </section>
    <!-- Testimonials section-->
    <section class="py-5 border-bottom">
        <div class="container px-5 my-5 px-5">
            <div class="text-center mb-5">
                <h2 class="fw-bolder">Brand Testimonials</h2>
                <p class="lead mb-0">What top brands say about us</p>
            </div>
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <!-- Testimonial 1 (Coca-Cola) -->
                    <div class="swiper-slide">
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-chat-right-quote-fill text-primary fs-1"></i>
                                    </div>
                                    <div class="ms-4">
                                        <p class="mb-1">"A truly fantastic experience working with this team. Their
                                            dedication and innovation are second to none. Highly recommended!"</p>
                                        <div class="small text-muted">- Coca-Cola, USA</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Testimonial 2 (Pepsi) -->
                    <div class="swiper-slide">
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-chat-right-quote-fill text-primary fs-1"></i>
                                    </div>
                                    <div class="ms-4">
                                        <p class="mb-1">"Exceptional quality and outstanding service. This platform made
                                            a real difference in our work!"</p>
                                        <div class="small text-muted">- Pepsi, USA</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Testimonial 3 (C2) -->
                    <div class="swiper-slide">
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-chat-right-quote-fill text-primary fs-1"></i>
                                    </div>
                                    <div class="ms-4">
                                        <p class="mb-1">"The platform is intuitive and easy to navigate. It’s a great
                                            tool for managing our needs and improving workflow efficiency!"</p>
                                        <div class="small text-muted">- C2, Vietnam</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>


    <!-- Contact section-->

    <!-- Footer-->

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
    <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
    <!-- * *                               SB Forms JS                               * *-->
    <!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
    <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
    <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
</body>

<footer style="background: linear-gradient(to right, #000000, #1a1a1a); color: white; padding: 60px 20px; font-family: 'Arial', sans-serif;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 30px;">
        <!-- Contact Information -->
        <div style="flex: 1; min-width: 250px;">
            <h3 style="font-size: 1.5rem; margin-bottom: 20px; color: #ffffff; border-bottom: 2px solid #333; padding-bottom: 10px;">
                Contact Us
            </h3>
            <div style="line-height: 1.8;">
                <p style="margin: 10px 0;">© 2024 Tuyên. All rights reserved.</p>
                <p style="margin: 10px 0;">
                    <i style="margin-right: 10px;">📍</i>
                    123 Street Name, City, Country
                </p>
                <p style="margin: 10px 0;">
                    <i style="margin-right: 10px;">📧</i>
                    <a href="mailto:tuyensample@example.com" style="color: #ffffff; text-decoration: none; transition: color 0.3s; hover: {color: #3498db};">
                        tuyensample@example.com
                    </a>
                </p>
            </div>
        </div>

        <!-- Map Section -->
        <div style="flex: 1; min-width: 300px;">
            <h3 style="font-size: 1.5rem; margin-bottom: 20px; color: #ffffff; border-bottom: 2px solid #333; padding-bottom: 10px;">
                Location
            </h3>
            <div style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d7445.275988606821!2d105.7152755!3d21.087116!3m2!1i1024!2i768!4f13.1!5e0!3m2!1svi!2s!4v1731210408424!5m2!1svi!2s"
                    width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        <!-- Social Media -->
        <div style="flex: 1; min-width: 250px;">
            <h3 style="font-size: 1.5rem; margin-bottom: 20px; color: #ffffff; border-bottom: 2px solid #333; padding-bottom: 10px;">
                Connect With Us
            </h3>
            <!-- Facebook Page Plugin -->
            <div style="margin-bottom: 20px; background: #1a1a1a; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                <div class="fb-page" 
                     data-href="https://www.facebook.com/profile.php?id=100083745145464"
                     data-tabs="timeline" 
                     data-width="300" 
                     data-height="250"
                     data-small-header="true"
                     data-adapt-container-width="true"
                     data-hide-cover="false"
                     data-show-facepile="true">
                </div>
            </div>

            <!-- Social Media Links -->
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <a href="https://twitter.com/yourprofile" target="_blank" 
                   style="color: #ffffff; text-decoration: none; padding: 8px 15px; 
                   background: rgba(255,255,255,0.05); border-radius: 5px; 
                   transition: all 0.3s; hover: {background: rgba(255,255,255,0.1)};">
                    Twitter
                </a>
                <a href="https://www.instagram.com/nguyenkhactuyen25/" target="_blank"
                   style="color: #ffffff; text-decoration: none; padding: 8px 15px; 
                   background: rgba(255,255,255,0.05); border-radius: 5px; 
                   transition: all 0.3s; hover: {background: rgba(255,255,255,0.1)};">
                    Instagram
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Facebook SDK -->
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" 
        src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0" 
        nonce="your-nonce-here">
</script>



</html>