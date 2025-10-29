<?php

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);


$conn = new mysqli("localhost", "root", "", "car_inventory");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $make = $_POST["make"];
    $model = $_POST["model"];
    $year = $_POST["year"];
    $vin = $_POST["vin"];
    $plate = $_POST["plate"];
    $fuel = $_POST["fuel"];
    $color = $_POST["color"];
    $category = $_POST["category"];
    $status = $_POST["status"];

    $photo = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = 'uploads/' . time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    $document = "";
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $document = 'uploads/' . time() . '_' . basename($_FILES['document']['name']);
        move_uploaded_file($_FILES['document']['tmp_name'], $document);
    }

    $stmt = $conn->prepare("INSERT INTO cars 
    (make, model, `year`, vin, plate, fuel, color, category, `status`, photo, document) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

   // ensure year is integer
$year = (int) $year;

$stmt->bind_param("ssissssssss",
    $make, $model, $year, $vin, $plate, $fuel, $color, $category, $status, $photo, $document
);


    if ($stmt->execute()) {
        $success = "Car added successfully!";
    } else {
        $error = "Error inserting: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Car - Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
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
            max-width: 900px;
            width: 100%;
            position: relative;
            z-index: 10;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--box-shadow);
            animation: slideIn 0.8s cubic-bezier(0.4, 0, 0.2, 1);
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

        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .page-title i {
            font-size: 2rem;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #667eea;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
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
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: block;
        }

        .file-input-label:hover {
            background: #f1f5f9;
            border-color: #667eea;
        }

        .file-input-label.has-file {
            background: #eff6ff;
            border-color: #667eea;
        }

        .file-icon {
            font-size: 2rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }

        .file-text {
            color: #6b7280;
            font-weight: 500;
        }

        .file-name {
            color: #667eea;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 1rem 2.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
            color: white;
        }

        .alert {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            border: none;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 768px) {
            .card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="bi bi-car-front-fill"></i>
                    Add New Car
                </h1>
                <p class="page-subtitle">Add a new vehicle to your inventory</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" id="carForm">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-info-circle"></i>
                        Basic Information
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Make</label>
                            <input type="text" name="make" class="form-control" required placeholder="e.g., Toyota, Honda, Ford" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" required placeholder="e.g., Camry, Civic, F-150" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" required min="1900" max="2030" placeholder="2024" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">VIN</label>
                            <input type="text" name="vin" class="form-control" required placeholder="17-character VIN" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Plate Number</label>
                            <input type="text" name="plate" class="form-control" required placeholder="License plate" />
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
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fuel Type</label>
                            <select name="fuel" class="form-select" required>
                                <option value="">Select Fuel Type</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Diesel">Diesel</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" name="color" class="form-control" required placeholder="e.g., White, Black, Silver" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="SUV">SUV</option>
                                <option value="Sedan">Sedan</option>
                                <option value="Hatchback">Hatchback</option>
                                <option value="Electric">Electric</option>
                                <option value="Luxury">Luxury</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Availability Status</label>
                            <select name="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Available">Available</option>
                                <option value="Booked">Booked</option>
                                <option value="Under Service">Under Service</option>
                                <option value="Sold">Sold</option>
                                <option value="Inactive">Inactive</option>
                            </select>
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Car Photo</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="photo" class="file-input" id="photo" accept="image/*">
                                <label for="photo" class="file-input-label" id="photoLabel">
                                    <div class="file-icon">
                                        <i class="bi bi-camera"></i>
                                    </div>
                                    <div class="file-text">Click to upload photo</div>
                                    <div class="file-name" id="photoName"></div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Car Document</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="document" class="file-input" id="document">
                                <label for="document" class="file-input-label" id="documentLabel">
                                    <div class="file-icon">
                                        <i class="bi bi-file-text"></i>
                                    </div>
                                    <div class="file-text">Click to upload document</div>
                                    <div class="file-name" id="documentName"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Car
                    </button>
                </div>
            </form>
        </div>
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
        document.getElementById('carForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Adding Car...';
            submitBtn.disabled = true;
        });

        // Form validation
        const form = document.getElementById('carForm');
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.style.borderColor = '#ef4444';
                } else {
                    this.style.borderColor = '#10b981';
                }
            });
        });
    </script>
</body>
</html>