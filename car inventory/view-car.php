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

$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($car_id > 0) {
    $sql = "SELECT * FROM cars WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
    $stmt->close();
} else {
    $car = null;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $car ? htmlspecialchars($car['make'] . ' ' . $car['model']) : 'Car Not Found'; ?> - Car Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --card-bg: rgba(255, 255, 255, 0.95);
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
            padding: 2rem 0;
            position: relative;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .car-details-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            padding: 0;
            box-shadow: var(--box-shadow);
            animation: slideIn 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .car-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .car-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="1" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .car-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .car-id {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .car-image-section {
            padding: 2rem;
            text-align: center;
            background: #f8fafc;
        }

        .car-image {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .car-image:hover {
            transform: scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .no-image {
            background: #e5e7eb;
            border-radius: 15px;
            padding: 4rem 2rem;
            color: #6b7280;
            font-size: 1.1rem;
        }

        .car-details {
            padding: 2.5rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .detail-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            transition: var(--transition);
        }

        .detail-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #667eea;
            font-size: 1.2rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.95rem;
        }

        .detail-value {
            font-weight: 700;
            color: #374151;
            font-size: 1rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
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

        .status-inactive {
            background: rgba(107, 114, 128, 0.1);
            color: #374151;
        }

        .document-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }

        .document-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            padding: 2.5rem;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            border: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
            box-shadow: 0 5px 15px rgba(107, 114, 128, 0.3);
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(75, 85, 99, 0.4);
            color: white;
        }

        .error-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ef4444;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #374151;
        }

        .error-text {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .car-title {
                font-size: 2rem;
            }

            .car-header {
                padding: 2rem 1rem;
            }

            .car-details {
                padding: 1.5rem;
            }

            .details-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .detail-section {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
                padding: 2rem 1rem;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($car): ?>
            <div class="car-details-card">
                <div class="car-header">
                    <h1 class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h1>
                    <p class="car-id">Vehicle ID: #<?php echo htmlspecialchars($car['id']); ?></p>
                </div>

                <?php if (!empty($car['photo'])): ?>
                    <div class="car-image-section">
                        <img src="<?php echo htmlspecialchars($car['photo']); ?>" 
                             alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>" 
                             class="car-image">
                    </div>
                <?php else: ?>
                    <div class="car-image-section">
                        <div class="no-image">
                            <i class="bi bi-camera mb-3" style="font-size: 3rem;"></i>
                            <p>No photo available for this vehicle</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="car-details">
                    <div class="details-grid">
                        <!-- Basic Information -->
                        <div class="detail-section">
                            <h3 class="section-title">
                                <i class="bi bi-info-circle"></i>
                                Basic Information
                            </h3>
                            <div class="detail-item">
                                <span class="detail-label">Make</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['make']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Model</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['model']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Year</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['year']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">VIN</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['vin']); ?></span>
                            </div>
                        </div>

                        <!-- Vehicle Details -->
                        <div class="detail-section">
                            <h3 class="section-title">
                                <i class="bi bi-gear"></i>
                                Vehicle Details
                            </h3>
                            <div class="detail-item">
                                <span class="detail-label">Plate Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['plate']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fuel Type</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['fuel']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Color</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['color']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Category</span>
                                <span class="detail-value"><?php echo htmlspecialchars($car['category']); ?></span>
                            </div>
                        </div>

                        <!-- Status & Documents -->
                        <div class="detail-section">
                            <h3 class="section-title">
                                <i class="bi bi-clipboard-check"></i>
                                Status & Documents
                            </h3>
                            <div class="detail-item">
                                <span class="detail-label">Status</span>
                                <span class="status-badge status-<?php echo strtolower($car['status']); ?>">
                                    <?php echo htmlspecialchars($car['status']); ?>
                                </span>
                            </div>
                            <div class="detail-item" style="flex-direction: column; align-items: flex-start;">
                                <span class="detail-label mb-2">Document</span>
                                <?php if (!empty($car['document'])): ?>
                                    <a href="<?php echo htmlspecialchars($car['document']); ?>" 
                                       target="_blank" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-file-earmark-text"></i>
                                        View Document
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No document uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="search.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to Search
                    </a>
                    <a href="edit.php?id=<?php echo $car['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i>
                        Edit Car
                    </a>
                    <a href="delete.php?id=<?php echo $car['id']; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this car? This action cannot be undone.');">
                        <i class="bi bi-trash"></i>
                        Delete Car
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="car-details-card">
                <div class="error-state">
                    <div class="error-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h2 class="error-title">Car Not Found</h2>
                    <p class="error-text">
                        The car you're looking for doesn't exist or may have been removed from the inventory.
                    </p>
                    <a href="search.php" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                        Search Cars
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Add loading states to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            if (btn.href && (btn.href.includes('edit.php') || btn.href.includes('delete.php'))) {
                btn.addEventListener('click', function(e) {
                    if (!this.href.includes('delete.php') || confirm('Are you sure?')) {
                        this.style.opacity = '0.7';
                        this.style.pointerEvents = 'none';
                    }
                });
            }
        });

        // Image zoom functionality
        const carImage = document.querySelector('.car-image');
        if (carImage) {
            carImage.addEventListener('click', function() {
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.9);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 10000;
                    cursor: zoom-out;
                `;
                
                const zoomedImage = document.createElement('img');
                zoomedImage.src = this.src;
                zoomedImage.style.cssText = `
                    max-width: 90%;
                    max-height: 90%;
                    object-fit: contain;
                    border-radius: 10px;
                `;
                
                modal.appendChild(zoomedImage);
                document.body.appendChild(modal);
                
                modal.addEventListener('click', function() {
                    document.body.removeChild(modal);
                });
            });
            
            carImage.style.cursor = 'zoom-in';
        }

        // Smooth entrance animation
        window.addEventListener('load', function() {
            const card = document.querySelector('.car-details-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>