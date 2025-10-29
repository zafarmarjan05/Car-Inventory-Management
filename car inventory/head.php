<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Car Inventory Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
    body {
      background: linear-gradient(to right, #4facfe, #00f2fe);
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      background: #343a40;
      padding: 1rem;
      font-family: 'castellar';
    }

    .navbar-brand {
      color: #fff !important;
      font-weight: bold;
    }

    .sidebar {
      background: #212529;
      color: #fff;
      min-height: 100vh;
      padding-top: 20px;
      position: fixed;
      width: 220px;
      top: 0;
      left: 0;
      transition: 0.3s;
    }

    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: #adb5bd;
      text-decoration: none;
      transition: 0.3s;
      font-size: 20px;
      font-family: 'Times New Roman';
    }

    .sidebar a:hover {
      background: #495057;
      color: #fff;
    }

    .dropdown-toggle::after {
      float: right;
      margin-top: 8px;
    }

    .dropdown-menu {
      background: #343a40;
      border: none;
      margin-left: 10px;
    }

    .dropdown-menu a {
      color: #adb5bd !important;
      font-size: 18px;
    }

    .dropdown-menu a:hover {
      background: #495057 !important;
      color: #fff !important;
    }

    .content {
      margin-left: 220px;
      padding: 20px;
      flex: 1;
    }

    .card {
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }

      .content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand mx-auto" href="#"> <i class="bi bi-car-front"></i> Car Inventory Management</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </nav>

  <!-- Sidebar -->
  <div class="sidebar">
    <a href="dashboard.php"> Dashboard</a>
    <a href="addcar.php"><i class="bi bi-plus-circle-fill"></i> Add Car</a>

    <!-- Dropdown for View Cars -->
    <div class="dropdown">
      <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-list-ul"></i> View Cars
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="view-cars.php">All Cars</a></li>
        <li><a class="dropdown-item" href="available-cars.php">Available Cars</a></li>
        <li><a class="dropdown-item" href="sold-cars.php">Sold Cars</a></li>
      </ul>
    </div>

    <a href="search.php"><i class="bi bi-search"></i> Search Car</a>
    <a href="trackavailability.php"><i class="bi bi-bar-chart"></i> Track Availability</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
