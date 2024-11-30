<?php
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "supplierdatabase";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Handle date selection form
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Get revenue data by supplier and total revenue
$supplierRevenueQuery = "SELECT s.SupplierName, SUM(i.TotalAmount) AS TotalRevenue 
                         FROM supplier s
                         JOIN invoice i ON s.id = i.SupplierID
                         WHERE (i.InvoiceDate >= ? AND i.InvoiceDate <= ?)
                         GROUP BY s.SupplierName
                         ORDER BY TotalRevenue DESC";
$stmt = $conn->prepare($supplierRevenueQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$supplierRevenueResult = $stmt->get_result();

$supplierRevenueData = [];
$totalRevenue = 0;
if ($supplierRevenueResult->num_rows > 0) {
    while ($row = $supplierRevenueResult->fetch_assoc()) {
        $supplierRevenueData[] = $row;
        $totalRevenue += $row['TotalRevenue'];
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Supplier Analytics Dashboard</title>
    
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="../css/styles.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
                    <li class="nav-item"><a class="nav-link" href="supplier.php">Suppliers</a></li>
                    <li class="nav-item"><a class="nav-link" href="invoice.php">Invoice</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page"
                            href="statistic.php">Statistics</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">Supplier Analytics Dashboard</h1>
        <form method="POST" action="" class="my-4">
            <div class="mb-3">
                <label for="start_date" class="form-label">Select Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control">
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">Select End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <!-- Download PDF button -->
        <button id="downloadPdf" class="btn btn-success mb-4">Download PDF</button>

        <!-- Supplier Revenue Bar Chart -->
        <div class="row my-4">
            <div class="col-12">
                <canvas id="supplierRevenueChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('supplierRevenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($supplierRevenueData, 'SupplierName')) ?>,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: <?= json_encode(array_column($supplierRevenueData, 'TotalRevenue')) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Revenue by Supplier',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ' + new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(context.raw);
                                }
                            }
                        }
                    }
                }
            });
        });

        document.getElementById("downloadPdf").addEventListener("click", function () {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape');

            // PDF Title
            pdf.text("Supplier Revenue Report", 10, 10);
            pdf.text(`From: <?= $startDate ?> To: <?= $endDate ?>`, 10, 20);

            // Convert chart to image and add to PDF
            html2canvas(document.getElementById("supplierRevenueChart")).then(function (canvas) {
                const chartImage = canvas.toDataURL("image/png");
                pdf.addImage(chartImage, 'PNG', 10, 30, 260, 130);

                // Add revenue details to PDF
                let yPosition = 170;
                pdf.text("Revenue Details:", 10, yPosition);
                yPosition += 10;
                <?php foreach ($supplierRevenueData as $index => $data): ?>
                    pdf.text("<?= $data['SupplierName'] ?>: " + new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(<?= $data['TotalRevenue'] ?>), 10, yPosition);
                    yPosition += 10;
                <?php endforeach; ?>

                // Save PDF
                pdf.save("SupplierRevenueReport.pdf");
            });
        });
    </script>
</body>
</html>