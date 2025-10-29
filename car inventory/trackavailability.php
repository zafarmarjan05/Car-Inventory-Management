<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "car_inventory");

// Check connection
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Query to fetch only available cars
$sql = "SELECT id, make, model, year, color, plate, photo FROM cars WHERE status = 'available' AND hide = 0";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Availability - Car Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        /* Cars Grid */
        .cars-container {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-title {
            color: #1f2937;
            font-size: 1.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #10b981;
            font-size: 1.5rem;
        }

        .cars-count {
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .car-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            border: 1px solid #f3f4f6;
        }

        .car-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .car-image {
            height: 200px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .car-card:hover .car-image img {
            transform: scale(1.05);
        }

        .no-image {
            color: #9ca3af;
            font-size: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .car-details {
            padding: 1.5rem;
        }

        .car-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .car-title i {
            color: #10b981;
        }

        .car-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #374151;
        }

        .availability-badge {
            background: rgba(34, 197, 94, 0.1);
            color: #065f46;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .availability-badge i {
            font-size: 0.75rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .empty-text {
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
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

            .page-title {
                font-size: 2.2rem;
            }

            .cars-container {
                padding: 1.5rem;
            }

            .cars-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(102, 126, 234, 0.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
                <i class="bi bi-bar-chart-fill"></i>
                Available Cars
            </h3>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link">
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
                <a href="available-cars.php" class="nav-link active">
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
            
           
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Available Cars</h1>
            <p class="page-subtitle">Monitor and track your available inventory</p>
        </div>

        <div class="cars-container">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-check-circle-fill"></i>
                    Currently Available
                </h2>
                <div class="cars-count">
                    <?php echo ($result && $result->num_rows > 0) ? $result->num_rows : 0; ?> Cars Available
                </div>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="cars-grid">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="car-card">
                            <div class="car-image">
                                <?php if (!empty($row['photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($row['make'] . ' ' . $row['model']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="bi bi-car-front"></i>
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="car-details">
                                <h3 class="car-title">
                                    <i class="bi bi-car-front"></i>
                                    <?php echo htmlspecialchars($row['make'] . ' ' . $row['model']); ?>
                                </h3>
                                
                                <div class="car-info">
                                    <div class="info-item">
                                        <span class="info-label">Year</span>
                                        <span class="info-value"><?php echo htmlspecialchars($row['year']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Color</span>
                                        <span class="info-value"><?php echo htmlspecialchars($row['color']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Plate No.</span>
                                        <span class="info-value"><?php echo htmlspecialchars($row['plate']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">ID</span>
                                        <span class="info-value">#<?php echo htmlspecialchars($row['id']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="availability-badge">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Available
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-car-front"></i>
                    </div>
                    <h3 class="empty-title">No Available Cars</h3>
                    <p class="empty-text">
                        There are currently no cars marked as available in your inventory.
                    </p>
                    <a href="addcar.php" class="btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Add New Car
                    </a>
                </div>
            <?php endif; ?>
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

        // Smooth entrance animations
        window.addEventListener('load', () => {
            const carCards = document.querySelectorAll('.car-card');
            carCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100 + 500);
            });
        });

        // Refresh functionality
        function refreshAvailability() {
            const countElement = document.querySelector('.cars-count');
            if (countElement) {
                countElement.innerHTML = '<span class="loading"></span> Refreshing...';
                
                // Simulate refresh (in real app, this would be an AJAX call)
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        }

        // Add refresh button functionality if needed
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                e.preventDefault();
                refreshAvailability();
            }
        });
    </script>
</body>
</html>

<?php
$mysqli->close();
?>