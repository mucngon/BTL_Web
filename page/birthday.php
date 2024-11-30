<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chúc mừng sinh nhật doanh nghiệp</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .birthday-card {
            border: 2px solid #ffd700;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .birthday-card:hover {
            transform: translateY(-5px);
        }
        .birthday-section {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>