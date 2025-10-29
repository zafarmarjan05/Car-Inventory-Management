<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'car_inventory');

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

$sql = "SELECT * FROM cars WHERE hide = 0 AND (make LIKE ? OR model LIKE ? OR year LIKE ?)";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Cars - Car Inventory</title>
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

        /* Search Section */
        .search-section {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-input {
            flex: 1;
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            transition: var(--transition);
            background: white;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .search-btn {
            background: var(--primary-gradient);
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            transition: var(--transition);
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Results Section */
        .results-section {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--box-shadow);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .results-header {
            margin-bottom: 2rem;
        }

        .results-title {
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .results-count {
            color: #6b7280;
            font-size: 1rem;
        }

        .table-responsive {
            border-radius: 15px;
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
            padding: 1.25rem;
            border: none;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            font-weight: 500;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background: rgba(34, 197, 94, 0.1);
            color: #065f46;
        }

        .status-sold {
            background: rgba(245, 158, 11, 0.1);
            color: #92400e;
        }

        .status-booked {
            background: rgba(59, 130, 246, 0.1);
            color: #1e40af;
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
        }

        .empty-text {
            font-size: 1rem;
            margin-bottom: 2rem;
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

            .search-form {
                flex-direction: column;
            }

            .search-btn {
                width: 100%;
            }

            .btn-group {
                flex-direction: column;
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
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
                <i class="bi bi-search"></i>
                Search Cars
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
                <a href="view-cars.php" class="nav-link">
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
                <a href="search.php" class="nav-link active">
                    <i class="bi bi-search"></i>
                    <span>Search Cars</span>
                </a>
            </div>
            
            
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Search Cars</h1>
            <p class="page-subtitle">Find the perfect vehicle in your inventory</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="get" action="search.php" class="search-form">
                <input 
                    type="text" 
                    class="search-input" 
                    name="q" 
                    value="<?php echo htmlspecialchars($search); ?>" 
                    placeholder="Search by make, model, or year..."
                    autocomplete="off"
                >
                <button type="submit" class="search-btn">
                    <i class="bi bi-search me-2"></i>
                    Search
                </button>
            </form>
        </div>

        <?php if ($search): ?>
            <!-- Results Section -->
            <div class="results-section">
                <div class="results-header">
                    <h2 class="results-title">Search Results</h2>
                    <p class="results-count">
                        Results for "<strong><?php echo htmlspecialchars($search); ?></strong>" 
                        (<?php echo $result ? $result->num_rows : 0; ?> found)
                    </p>
                </div>

                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Make</th>
                                    <th>Model</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['make']); ?></td>
                                        <td><?php echo htmlspecialchars($row['model']); ?></td>
                                        <td><?php echo htmlspecialchars($row['year']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a class="btn btn-primary btn-sm" href="view-car.php?id=<?php echo $row['id']; ?>">
                                                    <i class="bi bi-eye"></i>
                                                    View
                                                </a>
                                                <a class="btn btn-warning btn-sm" href="edit.php?id=<?php echo $row['id']; ?>">
                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit
                                                </a>
                                                <a class="btn btn-danger btn-sm" href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this car?');">
                                                    <i class="bi bi-trash"></i>
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="empty-title">No Results Found</h3>
                        <p class="empty-text">
                            We couldn't find any cars matching "<?php echo htmlspecialchars($search); ?>". 
                            Try searching with different keywords.
                        </p>
                        <a href="search.php" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Clear Search
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="results-section">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3 class="empty-title">Start Your Search</h3>
                    <p class="empty-text">
                        Enter a make, model, or year above to find cars in your inventory.
                    </p>
                </div>
            </div>
        <?php endif; ?>
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

        // Auto-focus search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });

        // Add loading state to search button
        document.querySelector('.search-form').addEventListener('submit', function() {
            const btn = document.querySelector('.search-btn');
            btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Searching...';
            btn.disabled = true;
        });
    </script>
</body>
</html>