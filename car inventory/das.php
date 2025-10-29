<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Car Inventory Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --dark-bg: #1a1d29;
      --card-bg: rgba(255, 255, 255, 0.95);
      --sidebar-bg: rgba(26, 29, 41, 0.98);
      --text-light: #8b949e;
      --border-radius: 15px;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: var(--success-gradient);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* Animated Background */
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
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding: 1rem 0;
      position: sticky;
      top: 0;
      z-index: 1030;
    }

    .navbar-brand {
      color: white !important;
      font-weight: 700;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .navbar-brand i {
      font-size: 2rem;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
      display: none;
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      padding: 0.5rem;
      border-radius: 8px;
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
      backdrop-filter: blur(10px);
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
      font-size: 1.25rem;
      margin: 0;
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
      border-radius: 12px;
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
      transform: translateX(5px);
    }

    .nav-link:hover::before {
      width: 100%;
    }

    .nav-link.active {
      background: var(--primary-gradient);
      color: white !important;
    }

    .nav-link i {
      font-size: 1.25rem;
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

    /* Dashboard Cards */
    .dashboard-header {
      margin-bottom: 3rem;
    }

    .dashboard-title {
      color: white;
      font-size: 2.5rem;
      font-weight: 700;
      text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
      margin-bottom: 0.5rem;
    }

    .dashboard-subtitle {
      color: rgba(255, 255, 255, 0.8);
      font-size: 1.1rem;
      font-weight: 400;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .stat-card {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      padding: 2rem;
      box-shadow: var(--box-shadow);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--primary-gradient);
    }

    .stat-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .stat-card.total::before {
      background: var(--primary-gradient);
    }

    .stat-card.available::before {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-card.sold::before {
      background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
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
      margin-bottom: 1.5rem;
    }

    .stat-card.total .stat-icon {
      background: var(--primary-gradient);
    }

    .stat-card.available .stat-icon {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-card.sold .stat-icon {
      background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
    }

    .stat-title {
      color: #6c757d;
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
    }

    .stat-value {
      color: #212529;
      font-size: 2.5rem;
      font-weight: 700;
      line-height: 1;
    }

    /* Quick Actions */
    .quick-actions {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      padding: 2rem;
      box-shadow: var(--box-shadow);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .quick-actions h4 {
      color: #212529;
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .action-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }

    .action-btn {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1.25rem;
      background: white;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      text-decoration: none;
      color: #495057;
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
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
        font-size: 2rem;
      }

      .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }

      .stat-card {
        padding: 1.5rem;
      }

      .action-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .dashboard-title {
        font-size: 1.75rem;
      }

      .stat-value {
        font-size: 2rem;
      }

      .quick-actions {
        padding: 1.5rem;
      }
    }

    /* Overlay for mobile */
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

    /* Loading Animation */
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Smooth scroll */
    html {
      scroll-behavior: smooth;
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
        <a href="view-cars.php" class="nav-link">
          <i class="bi bi-collection-fill"></i>
          <span>All Cars</span>
        </a>
      </div>
      
      <div class="nav-item">
        <a href="available-cars.php" class="nav-link">
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
      
      <div class="nav-item">
        <a href="trackavailability.php" class="nav-link">
          <i class="bi bi-bar-chart-fill"></i>
          <span>Track Availability</span>
        </a>
      </div>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">
    <div class="dashboard-header">
      <h1 class="dashboard-title">Welcome Back!</h1>
      <p class="dashboard-subtitle">Here's what's happening with your car inventory today.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon">
          <i class="bi bi-car-front"></i>
        </div>
        <div class="stat-title">Total Cars</div>
        <div class="stat-value">156</div>
      </div>
      
      <div class="stat-card available">
        <div class="stat-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-title">Available Cars</div>
        <div class="stat-value">89</div>
      </div>
      
      <div class="stat-card sold">
        <div class="stat-icon">
          <i class="bi bi-cart-check"></i>
        </div>
        <div class="stat-title">Sold Cars</div>
        <div class="stat-value">67</div>
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
        
        <a href="view-cars.php" class="action-btn">
          <i class="bi bi-collection"></i>
          <span>View All Cars</span>
        </a>
        
        <a href="trackavailability.php" class="action-btn">
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
    const mainContent = document.getElementById('mainContent');

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

    // Smooth animations on load
    window.addEventListener('load', () => {
      const statCards = document.querySelectorAll('.stat-card');
      statCards.forEach((card, index) => {
        setTimeout(() => {
          card.style.opacity = '0';
          card.style.transform = 'translateY(30px)';
          card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
          
          requestAnimationFrame(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          });
        }, index * 100);
      });
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
  </script>
</body>
</html>