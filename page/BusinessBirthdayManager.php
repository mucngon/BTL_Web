<?php
class BusinessBirthdayManager {
    private $conn;
    private static $displayed = false;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function displayBirthdayMessages() {
        if (self::$displayed) {
            return '';
        }
        
        $output = $this->getEnhancedStyles();
        $output .= $this->getAnimationScript();
        
        $currentDate = date('m-d');
        $upcomingDays = 7;
        
        // Lấy dữ liệu sinh nhật
        $birthdays = $this->getBirthdays($currentDate, $upcomingDays);
        
        $todayBirthdays = array_filter($birthdays, fn($b) => $b['birthday_type'] === 'today');
        $upcomingBirthdays = array_filter($birthdays, fn($b) => $b['birthday_type'] === 'upcoming');
        
        $output .= '<div class="birthday-container">';
        
        // Hiển thị sinh nhật hôm nay
        if (!empty($todayBirthdays)) {
            $output .= '<div class="birthday-section today-birthdays animate-fade-in">';
            $output .= '<div class="birthday-header">';
            $output .= '<img src="https://cdn-icons-png.flaticon.com/512/1404/1404945.png" class="birthday-icon" alt="Birthday">';
            $output .= '<h2>Happy birthday</h2>';
            $output .= '</div>';
            
            $output .= '<div class="birthday-cards-container">';
            foreach ($todayBirthdays as $business) {
                $age = $this->calculateAge($business['EstablishedDate']);
                $output .= $this->createEnhancedBirthdayCard($business, $age);
            }
            $output .= '</div>';
            $output .= '</div>';
        }
        
        // Hiển thị sinh nhật sắp tới
        if (!empty($upcomingBirthdays)) {
            $output .= '<div class="upcoming-section animate-fade-in">';
            $output .= '<div class="upcoming-header">';
            $output .= '<img src="https://cdn-icons-png.flaticon.com/512/2278/2278049.png" class="calendar-icon" alt="Calendar">';
            $output .= '<h3>Upcoming birthday</h3>';
            $output .= '</div>';
            
            $output .= '<div class="upcoming-cards-container">';
            foreach ($upcomingBirthdays as $business) {
                $daysUntil = $this->calculateDaysUntil($business['EstablishedDate']);
                $output .= $this->createEnhancedUpcomingCard($business, $daysUntil);
            }
            $output .= '</div>';
            $output .= '</div>';
        }
        
        // Không có sinh nhật
        if (empty($birthdays)) {
            $output .= '<div class="no-birthday-container animate-fade-in">';
            $output .= '<img src="https://cdn-icons-png.flaticon.com/512/3875/3875172.png" class="no-birthday-icon" alt="No birthdays">';
            $output .= '<p>There are no upcoming corporate anniversaries.</p>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        self::$displayed = true;
        return $output;
    }
    
    private function getBirthdays($currentDate, $upcomingDays) {
        $sql = "SELECT 
                    SupplierName, 
                    EstablishedDate,
                    DATE_FORMAT(EstablishedDate, '%m-%d') as birthday_date,
                    CASE 
                        WHEN DATE_FORMAT(EstablishedDate, '%m-%d') = :currentDate THEN 'today'
                        ELSE 'upcoming'
                    END as birthday_type
                FROM Supplier 
                WHERE (
                    DATE_FORMAT(EstablishedDate, '%m-%d') = :currentDate
                    OR (
                        DATE_FORMAT(EstablishedDate, '%m-%d') 
                        BETWEEN DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%m-%d')
                        AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL :days DAY), '%m-%d')
                    )
                )
                ORDER BY DATE_FORMAT(EstablishedDate, '%m-%d')";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'currentDate' => $currentDate,
            'days' => $upcomingDays
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function createEnhancedBirthdayCard($business, $age) {
        return "
        <div class='birthday-card animate-scale'>
            <div class='confetti-container'>
                <div class='confetti'></div>
                <div class='confetti'></div>
                <div class='confetti'></div>
            </div>
            <div class='card-content'>
                <div class='cake-icon'>🎂</div>
                <h4>{$business['SupplierName']}</h4>
                <div class='anniversary'>
                    <span class='age-number'>{$age}</span>
                    <span class='age-text'>Established year</span>
                </div>
                <p class='wishes'>Wishing your esteemed company continued growth and success!</p>
                <div class='established-date'>
                  Date of establishment: " . date('d/m/Y', strtotime($business['EstablishedDate'])) . "
                </div>
            </div>
        </div>";
    }
    
    private function createEnhancedUpcomingCard($business, $daysUntil) {
        return "
        <div class='upcoming-card animate-slide-in'>
            <div class='upcoming-content'>
                <div class='calendar-day'>{$daysUntil}</div>
                <div class='upcoming-info'>
                    <h5>{$business['SupplierName']}</h5>
                    <p>Remaining days</p>
                </div>
            </div>
        </div>";
    }
    
    private function getEnhancedStyles() {
        return '<style>
            .birthday-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                font-family: "Segoe UI", Arial, sans-serif;
            }
            
            .birthday-header, .upcoming-header {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 30px;
                gap: 15px;
            }
            
            .birthday-icon, .calendar-icon {
                width: 40px;
                height: 40px;
                object-fit: contain;
            }
            
            .birthday-header h2, .upcoming-header h3 {
                margin: 0;
                color: #2c3e50;
                font-weight: 600;
            }
            
            .birthday-cards-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 40px;
            }
            
            .birthday-card {
                background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
                border-radius: 20px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                padding: 25px;
                position: relative;
                overflow: hidden;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            .birthday-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            }
            
            .cake-icon {
                font-size: 2.5em;
                margin-bottom: 15px;
            }
            
            .card-content {
                text-align: center;
            }
            
            .card-content h4 {
                color: #2c3e50;
                font-size: 1.5em;
                margin-bottom: 15px;
            }
            
            .anniversary {
                margin: 20px 0;
            }
            
            .age-number {
                font-size: 2.5em;
                font-weight: bold;
                color: #e74c3c;
                display: block;
            }
            
            .age-text {
                color: #7f8c8d;
                font-size: 0.9em;
            }
            
            .wishes {
                color: #34495e;
                font-size: 1em;
                line-height: 1.6;
                margin: 15px 0;
            }
            
            .established-date {
                color: #95a5a6;
                font-size: 0.9em;
                margin-top: 15px;
            }
            
            .upcoming-cards-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
            }
            
            .upcoming-card {
                background: #fff;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                padding: 15px;
                transition: transform 0.3s ease;
            }
            
            .upcoming-card:hover {
                transform: translateX(5px);
            }
            
            .upcoming-content {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .calendar-day {
                background: #3498db;
                color: white;
                width: 50px;
                height: 50px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2em;
                font-weight: bold;
            }
            
            .upcoming-info h5 {
                margin: 0 0 5px 0;
                color: #2c3e50;
            }
            
            .upcoming-info p {
                margin: 0;
                color: #7f8c8d;
                font-size: 0.9em;
            }
            
            .no-birthday-container {
                text-align: center;
                padding: 40px;
                background: #f8f9fa;
                border-radius: 20px;
                margin-top: 20px;
            }
            
            .no-birthday-icon {
                width: 80px;
                height: 80px;
                margin-bottom: 20px;
            }
            
            /* Animations */
            .animate-fade-in {
                animation: fadeIn 0.8s ease-out;
            }
            
            .animate-scale {
                animation: scaleIn 0.5s ease-out;
            }
            
            .animate-slide-in {
                animation: slideIn 0.5s ease-out;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes scaleIn {
                from { transform: scale(0.9); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
            
            @keyframes slideIn {
                from { transform: translateX(-20px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            /* Confetti Animation */
            .confetti-container {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
            }
            
            .confetti {
                position: absolute;
                width: 10px;
                height: 10px;
                background: #ffd700;
                opacity: 0.7;
            }
            
            .confetti:nth-child(1) {
                left: 10%;
                animation: confetti-fall 3s linear infinite;
                background: #e74c3c;
            }
            
            .confetti:nth-child(2) {
                left: 50%;
                animation: confetti-fall 2.5s linear infinite;
                animation-delay: 1s;
                background: #3498db;
            }
            
            .confetti:nth-child(3) {
                left: 80%;
                animation: confetti-fall 3.5s linear infinite;
                animation-delay: 0.5s;
                background: #2ecc71;
            }
            
            @keyframes confetti-fall {
                0% { transform: translateY(-100%) rotate(0deg); }
                100% { transform: translateY(1000%) rotate(360deg); }
            }
            
            /* Responsive Design */
            @media (max-width: 768px) {
                .birthday-cards-container,
                .upcoming-cards-container {
                    grid-template-columns: 1fr;
                }
                
                .birthday-card {
                    margin: 10px;
                }
            }
        </style>';
    }
    
    private function getAnimationScript() {
        return '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // Add animation classes to elements
                const cards = document.querySelectorAll(".birthday-card, .upcoming-card");
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = "1";
                        card.style.transform = "translateY(0)";
                    }, index * 200);
                });
            });
        </script>';
    }
    
    private function calculateAge($establishedDate) {
        $established = new DateTime($establishedDate);
        $now = new DateTime();
        $age = $now->diff($established);
        return $age->y;
    }
    
    private function calculateDaysUntil($establishedDate) {
        $birthday = new DateTime($establishedDate);
        $birthday->setDate(date('Y'), $birthday->format('m'), $birthday->format('d'));
        
        $now = new DateTime();
        if ($birthday < $now) {
            $birthday->modify('+1 year');
        }
        
        return $now->diff($birthday)->days;
    }
}

// Sử dụng
try {
    if (!isset($birthdayManager)) {
        require_once 'config.php';
        require_once 'database.php';
        $db = new Database($config['db']);
        $conn = $db->getConnection();
        $birthdayManager = new BusinessBirthdayManager($conn);
    }
    echo $birthdayManager->displayBirthdayMessages();
} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">';
    echo 'Đã xảy ra lỗi khi hiển thị thông báo sinh nhật: ' . $e->getMessage();
    echo '</div>';
    // Ghi log lỗi
    error_log('Birthday Manager Error: ' . $e->getMessage());
}
?>