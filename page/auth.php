<?php
session_start();
$host = 'localhost';
$db = 'supplierdatabase';
$user = 'root';
$pass = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Xử lý đăng nhập
        if (isset($_POST['action']) && $_POST['action'] === 'login') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Sai tên đăng nhập hoặc mật khẩu.";
            }
        }
        
        // Xử lý đăng ký
        if (isset($_POST['action']) && $_POST['action'] === 'register') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];
            
            if ($password !== $password_confirm) {
                $error = "Mật khẩu xác nhận không khớp.";
            } else {
                try {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
                    $stmt->execute([
                        'username' => $username,
                        'password' => $hashed_password
                    ]);
                    $success = "Đăng ký thành công! Vui lòng đăng nhập.";
                } catch (PDOException $e) {
                    $error = "Lỗi đăng ký: " . $e->getMessage();
                }
            }
        }
    }
} catch (PDOException $e) {
    $error = "Kết nối thất bại: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Supplier List - Business Frontpage</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/login.css" rel="stylesheet" />
</head>
<body style="background-image: url('../uploads/hinh-anh-bien-dep-29.jpg'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
    
<div class="row">
    <div class="col-md-6 mx-auto p-0">
        <div class="card">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger m-3"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success m-3"><?php echo $success; ?></div>
            <?php endif; ?>
            <div class="login-box">
                <div class="login-snip">
                    <input id="tab-1" type="radio" name="tab" class="sign-in" checked>
                    <label for="tab-1" class="tab">Login</label>
                    <input id="tab-2" type="radio" name="tab" class="sign-up">
                    <label for="tab-2" class="tab">Sign Up</label>
                    <div class="login-space">
                        <!-- Login Form -->
                        <form method="POST" class="login">
                            <input type="hidden" name="action" value="login">
                            <div class="group">
                                <label for="login-username" class="label">Username</label>
                                <input id="login-username" name="username" type="text" class="input" placeholder="Enter your username" required>
                            </div>
                            <div class="group">
                                <label for="login-password" class="label">Password</label>
                                <input id="login-password" name="password" type="password" class="input" placeholder="Enter your password" required>
                            </div>
                            <div class="group">
                                <input id="check" type="checkbox" class="check" checked>
                                <label for="check"><span class="icon"></span> Keep me Signed in</label>
                            </div>
                            <div class="group">
                                <input type="submit" class="button" value="Sign In">
                            </div>
                        </form>
                        <!-- Sign Up Form -->
                        <form method="POST" class="sign-up-form">
                            <input type="hidden" name="action" value="register">
                            <div class="group">
                                <label for="signup-username" class="label">Username</label>
                                <input id="signup-username" name="username" type="text" class="input" placeholder="Create your Username" required>
                            </div>
                            <div class="group">
                                <label for="signup-password" class="label">Password</label>
                                <input id="signup-password" name="password" type="password" class="input" placeholder="Create your password" required>
                            </div>
                            <div class="group">
                                <label for="password_confirm" class="label">Repeat Password</label>
                                <input id="password_confirm" name="password_confirm" type="password" class="input" placeholder="Repeat your password" required>
                            </div>
                            <div class="group">
                                <input type="submit" class="button" value="Sign Up">
                            </div>
                        </form>
                    </div>
                </div>
            </div>   
        </div>
    </div>
</div>
</body>
</html>