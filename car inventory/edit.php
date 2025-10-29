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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$car = null;
if ($id > 0) {
    $result = $conn->query("SELECT * FROM cars WHERE id = $id");
    $car = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $car) {
    $make     = $conn->real_escape_string($_POST['make']);
    $model    = $conn->real_escape_string($_POST['model']);
    $year     = intval($_POST['year']);
    $vin      = $conn->real_escape_string($_POST['vin']);
    $plate    = $conn->real_escape_string($_POST['plate']);
    $fuel     = $conn->real_escape_string($_POST['fuel']);
    $color    = $conn->real_escape_string($_POST['color']);
    $category = $conn->real_escape_string($_POST['category']);
    $status   = $conn->real_escape_string($_POST['status']);

    // image upload
    $photo = $car['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $imagePath = 'uploads/' . $imageName;
        if (!is_dir('uploads/')) {
            mkdir('uploads/', true);
        }
        move_uploaded_file($_FILES['photo']['tmp_name'], $imagePath);
        $photo = $conn->real_escape_string($imagePath);
    }

    // document upload
    $document = $car['document'];
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $docName = uniqid() . '_' . basename($_FILES['document']['name']);
        $docPath = 'uploads/' . $docName;
        if (!is_dir('uploads/')) {
            mkdir('uploads/', true);
        }
        move_uploaded_file($_FILES['document']['tmp_name'], $docPath);
        $document = $conn->real_escape_string($docPath);
    }

    $sql = "
        UPDATE cars SET
            make     = '$make',
            model    = '$model',
            year     = $year,
            vin      = '$vin',
            plate    = '$plate',
            fuel     = '$fuel',
            color    = '$color',
            category = '$category',
            status   = '$status',
            photo    = '$photo',
            document = '$document'
        WHERE id = $id
    ";

    if ($conn->query($sql)) {
        header("Location: view-car.php?id=$id"); 
        exit;
    } else {
        $error = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car Details - <?php echo $car ? htmlspecialchars($car['make'] . ' ' . $car['model']) : 'Car Not Found'; ?></title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .edit-card {
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

        .edit-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
        }

        .edit-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .edit-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .edit-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .form-container {
            padding: 3rem;
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-title i {
            color: #667eea;
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
            font-size: 1rem;
            display: block;
        }

        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: var(--transition);
            background: white;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
            outline: none;
        }

        .current-file {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .file-icon {
            color: #667eea;
            font-size: 1.25rem;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            background: #f8fafc;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: block;
            width: 100%;
        }

        .file-input-label:hover {
            background: #f1f5f9;
            border-color: #667eea;
        }

        .file-input-label.has-file {
            background: #eff6ff;
            border-color: #667eea;
        }

        .upload-icon {
            font-size: 2rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }

        .upload-text {
            color: #6b7280;
            font-weight: 500;
        }

        .file-name {
            color: #667eea;
            font-weight: 600;
            margin-top: 0.5rem;
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
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
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
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .alert {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            border: none;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border-left: 4px solid #ef4444;
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

        @media (max-width: 768px) {
            .edit-title {
                font-size: 2rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .edit-header {
                padding: 2rem 1rem;
            }

            .form-container {
                padding: 2rem 1.5rem;
            }

            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($car): ?>
            <div class="edit-card">
                <div class="edit-header">
                    <h1 class="edit-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit Car Details
                    </h1>
                    <p class="edit-subtitle">
                        <?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?> - ID: #<?php echo htmlspecialchars($car['id']); ?>
                    </p>
                </div>

                <div class="form-container">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" id="editForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="bi bi-info-circle"></i>
                                Basic Information
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Make</label>
                                        <input type="text" class="form-control" name="make" value="<?php echo htmlspecialchars($car['make']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Model</label>
                                        <input type="text" class="form-control" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Year</label>
                                        <input type="number" class="form-control" name="year" value="<?php echo htmlspecialchars($car['year']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">VIN</label>
                                        <input type="text" class="form-control" name="vin" value="<?php echo htmlspecialchars($car['vin']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Plate Number</label>
                                        <input type="text" class="form-control" name="plate" value="<?php echo htmlspecialchars($car['plate']); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="bi bi-gear"></i>
                                Specifications
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Fuel Type</label>
                                        <select name="fuel" class="form-select" required>
                                            <option <?php if($car['fuel']=="Petrol") echo "selected"; ?>>Petrol</option>
                                            <option <?php if($car['fuel']=="Diesel") echo "selected"; ?>>Diesel</option>
                                            <option <?php if($car['fuel']=="Electric") echo "selected"; ?>>Electric</option>
                                            <option <?php if($car['fuel']=="Hybrid") echo "selected"; ?>>Hybrid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Color</label>
                                        <input type="text" class="form-control" name="color" value="<?php echo htmlspecialchars($car['color']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Category</label>
                                        <select name="category" class="form-select" required>
                                            <option <?php if($car['category']=="Sedan") echo "selected"; ?>>Sedan</option>
                                            <option <?php if($car['category']=="SUV") echo "selected"; ?>>SUV</option>
                                            <option <?php if($car['category']=="Hatchback") echo "selected"; ?>>Hatchback</option>
                                            <option <?php if($car['category']=="Luxury") echo "selected"; ?>>Luxury</option>
                                            <option <?php if($car['category']=="Electric") echo "selected"; ?>>Electric</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select" required>
                                            <option <?php if($car['status']=="Available") echo "selected"; ?>>Available</option>
                                            <option <?php if($car['status']=="Booked") echo "selected"; ?>>Booked</option>
                                            <option <?php if($car['status']=="Under Service") echo "selected"; ?>>Under Service</option>
                                            <option <?php if($car['status']=="Sold") echo "selected"; ?>>Sold</option>
                                            <option <?php if($car['status']=="Inactive") echo "selected"; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Files -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="bi bi-file-earmark"></i>
                                Files & Documents
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Car Photo</label>
                                        <?php if (!empty($car['photo'])): ?>
                                            <div class="current-file">
                                                <div class="file-info">
                                                    <i class="bi bi-image file-icon"></i>
                                                    <span>Current photo</span>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($car['photo']); ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                    View
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="file-input-wrapper">
                                            <input type="file" name="photo" class="file-input" id="photo" accept="image/*">
                                            <label for="photo" class="file-input-label" id="photoLabel">
                                                <div class="upload-icon">
                                                    <i class="bi bi-cloud-upload"></i>
                                                </div>
                                                <div class="upload-text">Click to upload new photo</div>
                                                <div class="file-name" id="photoName"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Car Document</label>
                                        <?php if (!empty($car['document'])): ?>
                                            <div class="current-file">
                                                <div class="file-info">
                                                    <i class="bi bi-file-text file-icon"></i>
                                                    <span>Current document</span>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($car['document']); ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                    View
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="file-input-wrapper">
                                            <input type="file" name="document" class="file-input" id="document">
                                            <label for="document" class="file-input-label" id="documentLabel">
                                                <div class="upload-icon">
                                                    <i class="bi bi-cloud-upload"></i>
                                                </div>
                                                <div class="upload-text">Click to upload new document</div>
                                                <div class="file-name" id="documentName"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="view-car.php?id=<?php echo $car['id']; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="updateBtn">
                                <i class="bi bi-check-circle"></i>
                                Update Car
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="edit-card">
                <div class="error-state">
                    <div class="error-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h2 class="error-title">Car Not Found</h2>
                    <p class="error-text">
                        The car you're trying to edit doesn't exist or may have been removed from the inventory.
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
        // File input handlers
        document.getElementById('photo').addEventListener('change', function(e) {
            const label = document.getElementById('photoLabel');
            const nameDiv = document.getElementById('photoName');
            
            if (e.target.files.length > 0) {
                label.classList.add('has-file');
                nameDiv.textContent = e.target.files[0].name;
            } else {
                label.classList.remove('has-file');
                nameDiv.textContent = '';
            }
        });

        document.getElementById('document').addEventListener('change', function(e) {
            const label = document.getElementById('documentLabel');
            const nameDiv = document.getElementById('documentName');
            
            if (e.target.files.length > 0) {
                label.classList.add('has-file');
                nameDiv.textContent = e.target.files[0].name;
            } else {
                label.classList.remove('has-file');
                nameDiv.textContent = '';
            }
        });

        // Form submission handler
        document.getElementById('editForm').addEventListener('submit', function() {
            const updateBtn = document.getElementById('updateBtn');
            updateBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
            updateBtn.disabled = true;
        });

        // Form validation
        const form = document.getElementById('editForm');
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.style.borderColor = '#ef4444';
                } else {
                    this.style.borderColor = '#10b981';
                }
            });

            input.addEventListener('input', function() {
                if (this.style.borderColor === 'rgb(239, 68, 68)' && this.value.trim() !== '') {
                    this.style.borderColor = '#e5e7eb';
                }
            });
        });

        // Auto-save indication (visual feedback)
        let changesMade = false;
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (!changesMade) {
                    changesMade = true;
                    document.title = '• ' + document.title;
                }
            });
        });

        // Warn before leaving if changes made
        window.addEventListener('beforeunload', function(e) {
            if (changesMade) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Remove warning on form submit
        form.addEventListener('submit', function() {
            changesMade = false;
        });

        // Smooth entrance animation
        window.addEventListener('load', function() {
            const card = document.querySelector('.edit-card');
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