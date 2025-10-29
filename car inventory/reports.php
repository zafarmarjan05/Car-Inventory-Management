<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "car_inventory");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all statistics
$totalCars = $conn->query("SELECT COUNT(*) as count FROM cars")->fetch_assoc()['count'];
$availableCars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE status = 'available' AND hide = 0")->fetch_assoc()['count'];
$soldCars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE status = 'sold'")->fetch_assoc()['count'];
$bookedCars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE status = 'booked'")->fetch_assoc()['count'];
$serviceCars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE status = 'under service'")->fetch_assoc()['count'];

// Category breakdown
$categoryResult = $conn->query("SELECT category, COUNT(*) as count FROM cars GROUP BY category");
$categories = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}

// Fuel type breakdown
$fuelResult = $conn->query("SELECT fuel, COUNT(*) as count FROM cars GROUP BY fuel");
$fuels = [];
while ($row = $fuelResult->fetch_assoc()) {
    $fuels[] = $row;
}

// Status breakdown
$statusResult = $conn->query("SELECT status, COUNT(*) as count FROM cars WHERE hide = 0 GROUP BY status");
$statuses = [];
while ($row = $statusResult->fetch_assoc()) {
    $statuses[] = $row;
}

// Top makes
$makesResult = $conn->query("SELECT make, COUNT(*) as count FROM cars GROUP BY make ORDER BY count DESC LIMIT 5");
$makes = [];
while ($row = $makesResult->fetch_assoc()) {
    $makes[] = $row;
}

$conn->close();

// Prepare data for charts
$categoryLabels = json_encode(array_column($categories, 'category'));
$categoryData = json_encode(array_column($categories, 'count'));
$fuelLabels = json_encode(array_column($fuels, 'fuel'));
$fuelData = json_encode(array_column($fuels, 'count'));
$statusLabels = json_encode(array_column($statuses, 'status'));
$statusData = json_encode(array_column($statuses, 'count'));
$makeLabels = json_encode(array_column($makes, 'make'));
$makeData = json_encode(array_column($makes, 'count'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Car Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --card-bg: rgba(255, 255, 255, 0.95);
            --sidebar-bg: rgba(26, 29, 41, 0.98);
            --text-light: #8b949e;
            --border-radius: 20px;
            --box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--success-gradient);
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: var(--sidebar-bg);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-brand i {
            font-size: 1.8rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            backdrop-filter: blur(15px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem 0;
            z-index: 1040;
            transform: translateX(0);
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
        }

        .sidebar-header h3 {
            color: white;
            font-weight: 700;
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-nav {
            padding: 0 1rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: var(--text-light) !important;
            text-decoration: none;
            border-radius: 15px;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: var(--primary-gradient);
            transition: var(--transition);
            z-index: -1;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateX(8px);
        }

        .nav-link:hover::before {
            width: 100%;
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: white !important;
        }

        .nav-link i {
            font-size: 1.2rem;
            min-width: 24px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            transition: var(--transition);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            color: white;
            font-size: 2.8rem;
            font-weight: 800;
            text-shadow: 2px 2px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.1rem;
            font-weight: 400;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats Overview */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.8s ease-out both;
        }

        .stat-box:nth-child(2) { animation-delay: 0.1s; }
        .stat-box:nth-child(3) { animation-delay: 0.2s; }
        .stat-box:nth-child(4) { animation-delay: 0.3s; }
        .stat-box:nth-child(5) { animation-delay: 0.4s; }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-icon.total { background: var(--primary-gradient); }
        .stat-icon.available { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
        .stat-icon.sold { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
        .stat-icon.booked { background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); }
        .stat-icon.service { background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            color: #1f2937;
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-change {
            font-size: 0.85rem;
            color: #10b981;
            margin-top: 0.75rem;
        }

        /* Charts Section */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.8s ease-out 0.5s both;
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chart-title i {
            color: #667eea;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        /* Table Section */
        .table-section {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.8s ease-out 0.6s both;
        }

        .table-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-title i {
            color: #667eea;
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            padding: 1rem;
            border: none;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
            font-weight: 500;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            padding: 0.5rem;
            border-radius: 10px;
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            backdrop-filter: blur(5px);
        }

        .sidebar-overlay.show {
            display: block;
        }

        @media (max-width: 1024px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .stats-overview {
                grid-template-columns: 1fr;
            }

            .charts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand mx-auto" href="dashboard.php">
                <i class="bi bi-car-front-fill"></i>
                Car Inventory Management
            </a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </div>
    </nav>

   <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </h3>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="bi bi-house-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="addcar.php" class="nav-link">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Add New Car</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="allcars.php" class="nav-link">
                    <i class="bi bi-collection-fill"></i>
                    <span>All Cars</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="trackavailability.php" class="nav-link">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Available Cars</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="sold-cars.php" class="nav-link">
                    <i class="bi bi-cart-check-fill"></i>
                    <span>Sold Cars</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="search.php" class="nav-link">
                    <i class="bi bi-search"></i>
                    <span>Search Cars</span>
                </a>
            </div>
            
            <!-- 
            
            -->
        </nav>
    </div>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">
                <i class="bi bi-graph-up"></i>
                Reports & Analytics
            </h1>
            <p class="page-subtitle">Comprehensive insights into your car inventory</p>
        </div>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="stat-box">
                <div class="stat-icon total">
                    <i class="bi bi-car-front"></i>
                </div>
                <div class="stat-label">Total Cars</div>
                <div class="stat-value"><?php echo $totalCars; ?></div>
                <div class="stat-change"><i class="bi bi-graph-up"></i> Fleet Size</div>
            </div>

            <div class="stat-box">
                <div class="stat-icon available">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-label">Available</div>
                <div class="stat-value"><?php echo $availableCars; ?></div>
                <div class="stat-change">
                    <?php echo $totalCars > 0 ? round(($availableCars / $totalCars) * 100) : 0; ?>% of Fleet
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon sold">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-label">Sold</div>
                <div class="stat-value"><?php echo $soldCars; ?></div>
                <div class="stat-change"><i class="bi bi-arrow-up"></i> Completed Sales</div>
            </div>

            <div class="stat-box">
                <div class="stat-icon booked">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-label">Booked</div>
                <div class="stat-value"><?php echo $bookedCars; ?></div>
                <div class="stat-change">Reserved</div>
            </div>

            <div class="stat-box">
                <div class="stat-icon service">
                    <i class="bi bi-wrench"></i>
                </div>
                <div class="stat-label">Under Service</div>
                <div class="stat-value"><?php echo $serviceCars; ?></div>
                <div class="stat-change">Being Maintained</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="bi bi-pie-chart"></i>
                    Vehicle Category Distribution
                </h3>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="bi bi-pie-chart"></i>
                    Fuel Type Distribution
                </h3>
                <div class="chart-wrapper">
                    <canvas id="fuelChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="bi bi-bar-chart"></i>
                    Status Breakdown
                </h3>
                <div class="chart-wrapper">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="bi bi-bar-chart"></i>
                    Top 5 Makes
                </h3>
                <div class="chart-wrapper">
                    <canvas id="makesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Makes Table -->
        <div class="table-section">
            <h3 class="table-title">
                <i class="bi bi-list-ul"></i>
                Top Vehicle Makes
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Make</th>
                            <th>Count</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($makes as $make): 
                            $percentage = ($make['count'] / $totalCars) * 100;
                        ?>
                            <tr>
                                <td><strong><?php echo $rank++; ?></strong></td>
                                <td><?php echo htmlspecialchars($make['make']); ?></td>
                                <td><?php echo $make['count']; ?></td>
                                <td><?php echo number_format($percentage, 1); ?>%</td>
                                <td>
                                    <div class="badge" style="background: var(--primary-gradient); color: white;">
                                        <?php echo $make['count']; ?> Units
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = 'auto';
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });

        // Chart configuration
        const chartColors = ['#667eea', '#10b981', '#f59e0b', '#3b82f6', '#ef4444'];

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $categoryLabels; ?>,
                datasets: [{
                    data: <?php echo $categoryData; ?>,
                    backgroundColor: chartColors,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: "'Inter', sans-serif", size: 12 },
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Fuel Chart
        const fuelCtx = document.getElementById('fuelChart').getContext('2d');
        new Chart(fuelCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $fuelLabels; ?>,
                datasets: [{
                    data: <?php echo $fuelData; ?>,
                    backgroundColor: chartColors,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: "'Inter', sans-serif", size: 12 },
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $statusLabels; ?>,
                datasets: [{
                    label: 'Number of Cars',
                    data: <?php echo $statusData; ?>,
                    backgroundColor: chartColors,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Makes Chart
        const makesCtx = document.getElementById('makesChart').getContext('2d');
        new Chart(makesCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $makeLabels; ?>,
                datasets: [{
                    label: 'Number of Cars',
                    data: <?php echo $makeData; ?>,
                    backgroundColor: '#667eea',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Animate stat values on page load
        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.innerHTML = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Run animations on page load
        window.addEventListener('load', () => {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach((stat, index) => {
                const finalValue = parseInt(stat.textContent);
                stat.textContent = '0';
                setTimeout(() => {
                    animateValue(stat, 0, finalValue, 1500);
                }, index * 200);
            });
        });

        // Refresh analytics data
        function refreshAnalytics() {
            location.reload();
        }

        // Keyboard shortcut to refresh
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'R') {
                e.preventDefault();
                refreshAnalytics();
            }
        });

        // Export functionality (can be extended to export data)
        window.exportAnalytics = function() {
            window.print();
        };

        // Smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>