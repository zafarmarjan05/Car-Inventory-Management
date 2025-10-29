<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "car_inventory");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$totalCarsResult = $conn->query("SELECT COUNT(*) AS total FROM cars");
$totalCars = $totalCarsResult->fetch_assoc()['total'];
$availableCarsResult = $conn->query("SELECT COUNT(*) AS available FROM cars WHERE status = 'available' and hide='0' ");
$availableCars = $availableCarsResult->fetch_assoc()['available'];

$soldCarsResult = $conn->query("SELECT COUNT(*) AS sold FROM cars WHERE status = 'sold'");
$soldCars = $soldCarsResult->fetch_assoc()['sold'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Inventory Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            --dark-bg: #1a1d29;
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
            background: var(--success-gradient);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
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
            color: white;
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
            transform: translateX(5px);
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

        /* Dashboard Header */
        .dashboard-header {
            margin-bottom: 3rem;
            text-align: center;
        }

        .dashboard-title {
            color: white;
            font-size: 3rem;
            font-weight: 800;
            text-shadow: 2px 2px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .dashboard-subtitle {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.2rem;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out both;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.2s;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-gradient);
        }

        .stat-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .stat-card.total::before {
            background: var(--primary-gradient);
        }

        .stat-card.available::before {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }

        .stat-card.sold::before {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .stat-card.total .stat-icon {
            background: var(--primary-gradient);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .stat-card.available .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }

        .stat-card.sold .stat-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }

        .stat-title {
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            color: #1f2937;
            font-size: 3rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.875rem;
            color: #10b981;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        .quick-actions h4 {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: white;
            border: 2px solid #f3f4f6;
            border-radius: 15px;
            text-decoration: none;
            color: #4b5563;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
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

        .action-btn:hover {
            color: white;
            border-color: transparent;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .action-btn:hover::before {
            width: 100%;
        }

        .action-btn i {
            font-size: 1.5rem;
            min-width: 24px;
        }

        /* Responsive Design */
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

            .dashboard-title {
                font-size: 2.2rem;
            }

            .dashboard-subtitle {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .stat-card {
                padding: 2rem;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }
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
        <div class="dashboard-header">
            <h1 class="dashboard-title">Welcome Back!</h1>
            <p class="dashboard-subtitle">Here's what's happening with your car inventory today</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="bi bi-car-front"></i>
                </div>
                <div class="stat-title">Total Cars</div>
                <div class="stat-value"><?php echo $totalCars; ?></div>
                <div class="stat-change">
                    <i class="bi bi-arrow-up"></i> Fleet Overview
                </div>
            </div>
            
            <div class="stat-card available">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-title">Available Cars</div>
                <div class="stat-value"><?php echo $availableCars; ?></div>
                <div class="stat-change">
                    <i class="bi bi-arrow-up"></i> Ready for Sale
                </div>
            </div>
            
            <div class="stat-card sold">
                <div class="stat-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-title">Sold Cars</div>
                <div class="stat-value"><?php echo $soldCars; ?></div>
                <div class="stat-change">
                    <i class="bi bi-arrow-up"></i> Completed Sales
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h4>
                <i class="bi bi-lightning-fill"></i>
                Quick Actions
            </h4>
            <div class="action-grid">
                <a href="addcar.php" class="action-btn">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Add New Car</span>
                </a>
                
                <a href="search.php" class="action-btn">
                    <i class="bi bi-search"></i>
                    <span>Search Cars</span>
                </a>
                
                <a href="view-car.php" class="action-btn">
                    <i class="bi bi-collection"></i>
                    <span>View All Cars</span>
                </a>
                
                <a href="reports.php" class="action-btn">
                    <i class="bi bi-graph-up"></i>
                    <span>Analytics</span>
                </a>
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

        // Add active state to current page
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });

        // Close mobile menu when clicking on nav links
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });

        // Counter animation
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

        // Animate counters on page load
        window.addEventListener('load', () => {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                stat.textContent = '0';
                animateValue(stat, 0, finalValue, 2000);
            });
        });
    </script>
</body>
</html>