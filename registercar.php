<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection and processing logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_car'])) {
    // Database configuration
    $dbHost = "localhost";
    $dbUser = "root";
    $dbPass = "";
    $dbName = "wheelbay";

    // Create connection
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8mb4");

    // File upload handling
    $uploadDir = 'uploads/car_images/';  // Added trailing slash here
    $uploadedImagePaths = [];
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB

    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Process file uploads
    if (!empty($_FILES['car_images']['name'][0])) {
        foreach ($_FILES['car_images']['name'] as $key => $name) {
            if ($_FILES['car_images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['car_images']['tmp_name'][$key];
                $fileSize = $_FILES['car_images']['size'][$key];
                $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                // Validate file
                if ($fileSize > $maxFileSize) {
                    $error = "File $name exceeds 5MB limit";
                    break;
                }
                if (!in_array($fileType, $allowedTypes)) {
                    $error = "Invalid file type for $name. Only JPG, PNG, GIF allowed";
                    break;
                }

                $newFileName = uniqid('car_', true) . '.' . $fileType;
                $destPath = $uploadDir . $newFileName;  // Correct path concatenation

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $uploadedImagePaths[] = $newFileName;
                } else {
                    $error = "Failed to move uploaded file $name";
                    break;
                }
            }
        }
    } else {
        $error = "No images were uploaded";
    }

    // Process form data if no upload errors
    if (!isset($error)) {
        // Sanitize and validate inputs
        $make = $conn->real_escape_string(trim($_POST['make']));
        $model = $conn->real_escape_string(trim($_POST['model']));
        $year = (int)$_POST['year'];
        $mileage = (int)$_POST['mileage'];
        $fuel_type = $conn->real_escape_string(trim($_POST['fuel_type']));
        $transmission = $conn->real_escape_string(trim($_POST['transmission']));
        $color = $conn->real_escape_string(trim($_POST['color']));
        $price = (float)$_POST['price'];
        $description = $conn->real_escape_string(trim($_POST['description']));
        $imagesString = implode(',', $uploadedImagePaths);

        // Insert into database
        $sql = "INSERT INTO cars (make, model, year, mileage, fuel_type, transmission, color, price, description, images) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ssiisssdss', $make, $model, $year, $mileage, $fuel_type, 
                $transmission, $color, $price, $description, $imagesString);
            
            if ($stmt->execute()) {
                $success = "Car listing submitted successfully!";
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Your Car - WheelBay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #121212;
            color: #ffffff;
            line-height: 1.6;
        }

        .admin-header {
            background-color: #1a1a1a;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .admin-title {
            font-size: 1.8em;
            color: #17fee3;
        }

        .admin-nav {
            display: flex;
            gap: 20px;
        }

        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .admin-nav a:hover, .admin-nav a.active {
            background-color: #17fee3;
            color: #121212;
        }

        .admin-logout {
            color: #ff3333;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }

        .heading {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
            color: #17fee3;
        }

        .form-section {
            background-color: #1f1f1f;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #17fee3;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: none;
            background-color: #262c37;
            color: white;
            font-size: 16px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: 2px solid #17fee3;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .image-upload {
            border: 2px dashed #666;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .image-upload:hover {
            border-color: #17fee3;
            background-color: rgba(23, 254, 227, 0.05);
        }

        .image-upload i {
            font-size: 3em;
            margin-bottom: 15px;
            color: #666;
        }

        .image-upload p {
            color: #666;
            font-size: 1.1em;
        }

        .image-upload:hover i,
        .image-upload:hover p {
            color: #17fee3;
        }

        #thumbnail-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .preview-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
            position: relative;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #ff3333;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            border: 2px solid white;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #17fee3;
            color: #121212;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 1.1em;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #14d3c3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 254, 227, 0.3);
        }

        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }

        .status-message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
        }

        .success {
            background-color: rgba(23, 254, 227, 0.2);
            color: #17fee3;
            border-left: 4px solid #17fee3;
        }

        .error {
            background-color: rgba(255, 51, 51, 0.2);
            color: #ff3333;
            border-left: 4px solid #ff3333;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 80px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1 class="admin-title">WheelBay Admin Panel</h1>
        <div class="admin-nav">
            <a href="admin.php">Dashboard</a>
            <a href="registercar.php"  class="active" >New Car</a>
        </div>
        <a href="#" class="admin-logout">Logout <i class="fas fa-sign-out-alt"></i></a>
    </header>

    <div class="container">
        <h1 class="heading">Sell Your Car</h1>
        
        <?php if (isset($success)): ?>
            <div class="status-message success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="status-message error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <form id="car-form" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="make">Make</label>
                        <select id="make" name="make" required>
                            <option value="">Select Make</option>
                            <option value="Ferrari">Ferrari</option>
                            <option value="Lamborghini">Lamborghini</option>
                            <option value="Tesla">Tesla</option>
                            <option value="BMW">BMW</option>
                            <option value="Mercedes">Mercedes</option>
                            <option value="Audi">Audi</option>
                            <option value="Porsche">Porsche</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mileage">Mileage (miles)</label>
                        <input type="number" id="mileage" name="mileage" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type" required>
                            <option value="">Select Fuel Type</option>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="transmission">Transmission</label>
                        <select id="transmission" name="transmission" required>
                            <option value="">Select Transmission</option>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                            <option value="Semi-Automatic">Semi-Automatic</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Asking Price ($)</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Tell potential buyers about your car..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Upload Images (Max 5MB each)</label>
                    <div class="image-upload" id="image-upload-button">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click or drag images to upload</p>
                        <input type="file" id="file-input" name="car_images[]" accept="image/*" multiple style="display: none;">
                    </div>
                    <div id="thumbnail-preview"></div>
                </div>
                
                <button type="submit" name="submit_car" class="btn btn-block">SUBMIT CAR LISTING</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image upload handling
            const imageUploadButton = document.getElementById('image-upload-button');
            const fileInput = document.getElementById('file-input');
            const thumbnailPreview = document.getElementById('thumbnail-preview');
            const maxFiles = 10;

            imageUploadButton.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (e) => {
                thumbnailPreview.innerHTML = '';
                
                if (fileInput.files.length > maxFiles) {
                    alert(`You can only upload up to ${maxFiles} images`);
                    fileInput.value = '';
                    return;
                }

                for (const file of fileInput.files) {
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        const previewContainer = document.createElement('div');
                        previewContainer.style.position = 'relative';
                        previewContainer.style.display = 'inline-block';
                        
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'preview-image';
                        
                        const removeBtn = document.createElement('div');
                        removeBtn.className = 'remove-image';
                        removeBtn.innerHTML = '&times;';
                        removeBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            previewContainer.remove();
                            // Remove the file from the FileList (more complex in JS)
                        });
                        
                        previewContainer.appendChild(img);
                        previewContainer.appendChild(removeBtn);
                        thumbnailPreview.appendChild(previewContainer);
                    }
                    
                    reader.readAsDataURL(file);
                }
            });

            // Drag and drop functionality
            imageUploadButton.addEventListener('dragover', (e) => {
                e.preventDefault();
                imageUploadButton.style.borderColor = '#17fee3';
                imageUploadButton.style.backgroundColor = 'rgba(23, 254, 227, 0.1)';
            });

            imageUploadButton.addEventListener('dragleave', () => {
                imageUploadButton.style.borderColor = '#666';
                imageUploadButton.style.backgroundColor = '';
            });

            imageUploadButton.addEventListener('drop', (e) => {
                e.preventDefault();
                imageUploadButton.style.borderColor = '#666';
                imageUploadButton.style.backgroundColor = '';
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            });

            // Form validation
            const form = document.getElementById('car-form');
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = '#ff3333';
                        valid = false;
                    } else {
                        field.style.borderColor = '';
                    }
                });

                // Check if at least one image is uploaded
                if (fileInput.files.length === 0) {
                    alert('Please upload at least one image of your car');
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });
    </script>
</body>
</html>