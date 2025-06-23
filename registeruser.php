<?php
// **FIX 1: START THE SESSION**
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ... (your database connection code is fine) ...
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (your validation code is fine) ...
    $firstName = isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8') : '';
    $lastName = isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8') : '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8') : '';
    $dob = $_POST['dob'];
    $houseNo = isset($_POST['house_no']) ? htmlspecialchars($_POST['house_no'], ENT_QUOTES, 'UTF-8') : '';
    $street = isset($_POST['street']) ? htmlspecialchars($_POST['street'], ENT_QUOTES, 'UTF-8') : '';
    $city = isset($_POST['city']) ? htmlspecialchars($_POST['city'], ENT_QUOTES, 'UTF-8') : '';
    $state = isset($_POST['state']) ? htmlspecialchars($_POST['state'], ENT_QUOTES, 'UTF-8') : '';
    $country = isset($_POST['country']) ? htmlspecialchars($_POST['country'], ENT_QUOTES, 'UTF-8') : '';
    $zipCode = isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code'], ENT_QUOTES, 'UTF-8') : '';
    $licenseNumber = isset($_POST['license_number']) ? htmlspecialchars($_POST['license_number'], ENT_QUOTES, 'UTF-8') : '';
    $licenseCountry = isset($_POST['license_country']) ? htmlspecialchars($_POST['license_country'], ENT_QUOTES, 'UTF-8') : '';
    $licenseExpiry = $_POST['license_expiry'];
    $terms = isset($_POST['terms']);

    // ... (your validation logic is fine) ...
    // ...
    if (!$terms) {
        $errors['terms'] = 'You must accept the terms and conditions';
    }
    // ...

    // Handle file upload
    $profileImagePath = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        // ... (your file upload logic is fine) ...
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif','image/jpg'];
        $fileType = $_FILES['profile_image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/profile_images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                $profileImagePath = $destination;
            } else {
                $errors['profile_image'] = 'Failed to save uploaded file';
            }
        } else {
            $errors['profile_image'] = 'Only JPG, PNG, and GIF images are allowed';
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // **FIX 2: Convert boolean to integer for DB**
            $termsAccepted = $terms ? 1 : 0;
            
            // **FIX 3: Added `terms_accepted` to SQL query**
            $stmt = $conn->prepare("INSERT INTO users (
                profile_image, first_name, last_name, email, password, phone, dob,
                house_no, street, city, state, country, zip_code,
                license_number, license_country, license_expiry, terms_accepted
            ) VALUES (
                :profile_image, :first_name, :last_name, :email, :password, :phone, :dob,
                :house_no, :street, :city, :state, :country, :zip_code,
                :license_number, :license_country, :license_expiry, :terms_accepted
            )");
            
            $stmt->bindParam(':profile_image', $profileImagePath); 
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':house_no', $houseNo);
            $stmt->bindParam(':street', $street);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':state', $state);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':zip_code', $zipCode);
            $stmt->bindParam(':license_number', $licenseNumber);
            $stmt->bindParam(':license_country', $licenseCountry);
            $stmt->bindParam(':license_expiry', $licenseExpiry);
            // **FIX 4: Bind the new parameter**
            $stmt->bindParam(':terms_accepted', $termsAccepted, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $success = true;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            } else {
                $errors['database'] = 'Registration failed. Please try again.';
            }
        } catch(PDOException $e) {
            $errors['database'] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | WheelBay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        /* General styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .wheelbay-body {
            background-color: #121212;
            color: white;
            margin: 0;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header and navigation bar */
        .wheelbay-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 25px;
            height: 80px;
            transition: all 0.3s ease;
        }
        .wheelbay-header.scrolled {
            background-color: rgba(18, 18, 18, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            height: 70px;
        }

        .wheelbay-logo {
            width: 120px;
            transition: transform 0.3s;
        }

        .wheelbay-logo:hover {
            transform: scale(1.1);
        }

        /* Navigation links with hover effect */
        nav a {
            position: relative;
            font-size: 1.1em;
            color: #fff;
            text-decoration: none;
            padding: 6px 20px;
            transition: .5s;
        }

        nav a:hover {
            color: #0ef;
        }

        nav a span {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            border-bottom: 2px solid #0ef;
            border-radius: 15px;
            transform: scale(0) translateY(50px);
            opacity: 0;
            transition: .5s;
        }

        nav a:hover span {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* Main content */
        .wheelbay-main {
            flex: 1;
            padding: 40px;
            margin-top: 100px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
        }

        /* Registration Container */
        .registration-container {
            background-color: #1f1f1f;
            border-radius: 15px;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .registration-header h1 {
            font-size: 2.5em;
            color: #0ef;
            margin-bottom: 10px;
        }

        .registration-header p {
            color: #aaa;
            font-size: 1.1em;
        }

        /* Form Styles */
        .registration-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0ef;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: none;
            background-color: #262626;
            color: white;
            font-size: 1em;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            box-shadow: 0 0 0 2px #0ef;
        }

        .form-group .input-with-icon {
            position: relative;
        }

        .form-group .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #0ef;
        }

        .form-group .input-with-icon input {
            padding-left: 45px;
        }

        .password-strength {
            margin-top: 10px;
            height: 5px;
            background-color: #333;
            border-radius: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            background-color: #ff3333;
            transition: width 0.3s, background-color 0.3s;
        }

        .password-requirements {
            margin-top: 5px;
            font-size: 0.8em;
            color: #aaa;
        }

        .password-requirements ul {
            padding-left: 20px;
            margin-top: 5px;
        }

        .password-requirements li {
            margin-bottom: 3px;
        }

        .password-requirements .valid {
            color: #0ef;
        }

        .password-requirements .invalid {
            color: #ff3333;
        }

        .terms-group {
            display: flex;
            align-items: flex-start;
            margin-top: 20px;
            grid-column: 1 / -1;
        }

        .terms-group input {
            margin-right: 10px;
            margin-top: 3px;
        }

        .terms-group label {
            color: #ccc;
            font-size: 0.95em;
        }

        .terms-group label a {
            color: #0ef;
            text-decoration: none;
        }

        .terms-group label a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #ff3333;
            font-size: 0.85em;
            margin-top: 5px;
            display: none;
        }

        .input-error {
            box-shadow: 0 0 0 2px #ff3333 !important;
        }

        /* Address Section */
        .address-section {
            grid-column: 1 / -1;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }

        .address-section h3 {
            color: #0ef;
            margin-bottom: 20px;
            font-size: 1.3em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .address-section h3 i {
            font-size: 1.2em;
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        /* User Type Selection */
        .user-type-selection {
            grid-column: 1 / -1;
            margin-bottom: 15px;
        }

        .user-type-options {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .user-type-option {
            flex: 1;
            position: relative;
        }

        .user-type-option input {
            position: absolute;
            opacity: 0;
        }

        .user-type-option label {
            display: block;
            padding: 15px;
            background: #262626;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .user-type-option input:checked + label {
            border-color: #0ef;
            background: rgba(0, 238, 255, 0.1);
        }

        .user-type-option i {
            font-size: 1.5em;
            color: #0ef;
            margin-bottom: 10px;
            display: block;
        }

        /* Button Styles */
        .btn-group {
            grid-column: 1 / -1;
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, #00d2ff, #0ef);
            color: black;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 238, 255, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #0ef;
            border: 2px solid #0ef;
            margin-right: 15px;
        }

        .btn-secondary:hover {
            background: rgba(0, 238, 255, 0.1);
            transform: translateY(-3px);
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            grid-column: 1 / -1;
            color: #aaa;
        }

        .login-link a {
            color: #0ef;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Profile Picture Upload */
        .profile-upload {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-upload-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0ef;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #262626;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .profile-upload-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 238, 255, 0.3);
        }

        .profile-upload-preview i {
            font-size: 2.5em;
            color: #0ef;
        }

        .profile-upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-upload-text {
            color: #aaa;
            font-size: 0.9em;
            margin-top: 5px;
        }

        /* Footer */
        .wheelbay-footer {
            background-color: #1f1f1f;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 80px;
        }

        .wheelbay-footer p {
            margin: 0;
        }

        .wheelbay-social-links {
            margin-top: 20px;
        }

        .wheelbay-social-links a {
            margin: 0 10px;
            font-size: 1.5em;
            color: #17fee3;
            transition: all 0.3s;
            display: inline-block;
        }

        .wheelbay-social-links a:hover {
            color: #14d3c3;
            transform: translateY(-3px);
        }

        /* Success Modal */
        .success-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }

        .success-modal-content {
            background-color: #1f1f1f;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            text-align: center;
            animation: modalopen 0.5s;
        }

        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .success-modal h2 {
            color: #0ef;
            margin-bottom: 20px;
        }

        .success-modal p {
            color: #ccc;
            margin-bottom: 30px;
        }

        .success-modal .btn {
            margin-top: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .wheelbay-header {
                padding: 0 15px;
            }
            
            nav a {
                padding: 6px 12px;
                font-size: 1em;
            }
            
            .wheelbay-main {
                padding: 20px;
                margin-top: 80px;
            }
            
            .registration-container {
                padding: 30px 20px;
            }
            
            .registration-header h1 {
                font-size: 2em;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-secondary {
                margin-right: 0;
            }
            
            .address-grid {
                grid-template-columns: 1fr;
            }

            .user-type-options {
                flex-direction: column;
            }

            .success-modal-content {
                width: 90%;
                margin: 30% auto;
            }
        }

    </style>
</head>
<body class="wheelbay-body">
    <!-- Header with logo and navigation -->
    <header class="wheelbay-header">
        <img class="wheelbay-logo" src="img/Logo01.png" alt="WheelBay Logo">
        <nav>
            <a href="home.html">Home<span></span></a>
            <a href="cars.html">Cars<span></span></a>
            <a href="about.html">About<span></span></a>
            <a href="login.html">Login<span></span></a>
        </nav>
    </header>

    <!-- Main content -->
    <main class="wheelbay-main">
        <div class="registration-container">
            <div class="registration-header">
                <h1>Create Your Account</h1>
                <p>Join WheelBay to explore luxury cars and manage your dream collection</p>
            </div>
            
            <?php if (!empty($errors['database'])): ?>
                <div style="color: red; background-color: #2a0000; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #ff3333;">
                    <?php echo htmlspecialchars($errors['database']); ?>
                </div>
            <?php endif; ?>
            
            <form class="registration-form" id="register-form" method="POST" enctype="multipart/form-data" action="registeruser.php" novalidate>
                <!-- Profile Picture Upload -->
                <div class="profile-upload">
                    <div class="profile-upload-preview" id="profile-upload-preview">
                        <i class="fas fa-user"></i>
                        <img id="profile-preview-image" style="display: none;">
                    </div>
                    <div class="profile-upload-text">Click to upload profile picture</div>
                    <input type="file" id="profile-image-upload" name="profile_image" accept="image/*" style="display: none;">
                    <!-- **FIX 5: Make server-side errors visible** -->
                    <div class="error-message" id="profile-image-error" style="<?php if (!empty($errors['profile_image'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['profile_image'])) echo htmlspecialchars($errors['profile_image']); ?>
                    </div>
                </div>
                
                <!-- Basic Information -->
                <div class="form-group">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" name="first_name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                    <div class="error-message" id="first-name-error" style="<?php if (!empty($errors['first_name'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['first_name'])) echo htmlspecialchars($errors['first_name']); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last_name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                    <div class="error-message" id="last-name-error" style="<?php if (!empty($errors['last_name'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['last_name'])) echo htmlspecialchars($errors['last_name']); ?>
                    </div>
                </div>
                
                <!-- Email and Password Section -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="error-message" id="email-error" style="<?php if (!empty($errors['email'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['email'])) echo htmlspecialchars($errors['email']); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="password-strength"><div class="password-strength-bar" id="password-strength-bar"></div></div>
                    <div class="password-requirements">
                        <p>Password must contain:</p>
                        <ul><li id="req-length" class="invalid">At least 8 characters</li><li id="req-uppercase" class="invalid">1 uppercase letter</li><li id="req-number" class="invalid">1 number</li><li id="req-special" class="invalid">1 special character</li></ul>
                    </div>
                    <div class="error-message" id="password-error" style="<?php if (!empty($errors['password'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['password'])) echo htmlspecialchars($errors['password']); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm-password" name="confirm_password" required>
                    </div>
                    <div class="error-message" id="confirm-password-error" style="<?php if (!empty($errors['confirm_password'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['confirm_password'])) echo htmlspecialchars($errors['confirm_password']); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="error-message" id="phone-error" style="<?php if (!empty($errors['phone'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['phone'])) echo htmlspecialchars($errors['phone']); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
                    <div class="error-message" id="dob-error" style="<?php if (!empty($errors['dob'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['dob'])) echo htmlspecialchars($errors['dob']); ?>
                    </div>
                </div>
                
                <!-- Address Section -->
                <div class="address-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
                    <div class="address-grid">
                        <div class="form-group"><label for="house-no">House/Unit Number</label><input type="text" id="house-no" name="house_no" required value="<?php echo isset($_POST['house_no']) ? htmlspecialchars($_POST['house_no']) : ''; ?>"><div class="error-message" id="house-no-error" style="<?php if (!empty($errors['house_no'])) echo 'display: block;'; ?>"><?php if (!empty($errors['house_no'])) echo htmlspecialchars($errors['house_no']); ?></div></div>
                        <div class="form-group"><label for="street">Street</label><input type="text" id="street" name="street" required value="<?php echo isset($_POST['street']) ? htmlspecialchars($_POST['street']) : ''; ?>"><div class="error-message" id="street-error" style="<?php if (!empty($errors['street'])) echo 'display: block;'; ?>"><?php if (!empty($errors['street'])) echo htmlspecialchars($errors['street']); ?></div></div>
                        <div class="form-group"><label for="city">City</label><input type="text" id="city" name="city" required value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>"><div class="error-message" id="city-error" style="<?php if (!empty($errors['city'])) echo 'display: block;'; ?>"><?php if (!empty($errors['city'])) echo htmlspecialchars($errors['city']); ?></div></div>
                        <div class="form-group"><label for="state">State/Province</label><input type="text" id="state" name="state" required value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>"><div class="error-message" id="state-error" style="<?php if (!empty($errors['state'])) echo 'display: block;'; ?>"><?php if (!empty($errors['state'])) echo htmlspecialchars($errors['state']); ?></div></div>
                        <div class="form-group"><label for="country">Country</label><select id="country" name="country" required><option value="">Select Country</option><option value="US" <?php echo (isset($_POST['country']) && $_POST['country'] == 'US') ? 'selected' : ''; ?>>United States</option><option value="UK" <?php echo (isset($_POST['country']) && $_POST['country'] == 'UK') ? 'selected' : ''; ?>>United Kingdom</option><option value="CA" <?php echo (isset($_POST['country']) && $_POST['country'] == 'CA') ? 'selected' : ''; ?>>Canada</option><option value="AU" <?php echo (isset($_POST['country']) && $_POST['country'] == 'AU') ? 'selected' : ''; ?>>Australia</option><option value="LK" <?php echo (isset($_POST['country']) && $_POST['country'] == 'LK') ? 'selected' : ''; ?>>Sri Lanka</option><option value="IN" <?php echo (isset($_POST['country']) && $_POST['country'] == 'IN') ? 'selected' : ''; ?>>India</option><option value="JP" <?php echo (isset($_POST['country']) && $_POST['country'] == 'JP') ? 'selected' : ''; ?>>Japan</option><option value="DE" <?php echo (isset($_POST['country']) && $_POST['country'] == 'DE') ? 'selected' : ''; ?>>Germany</option><option value="FR" <?php echo (isset($_POST['country']) && $_POST['country'] == 'FR') ? 'selected' : ''; ?>>France</option></select><div class="error-message" id="country-error" style="<?php if (!empty($errors['country'])) echo 'display: block;'; ?>"><?php if (!empty($errors['country'])) echo htmlspecialchars($errors['country']); ?></div></div>
                        <div class="form-group"><label for="zip-code">Zip/Postal Code</label><input type="text" id="zip-code" name="zip_code" required value="<?php echo isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : ''; ?>"><div class="error-message" id="zip-code-error" style="<?php if (!empty($errors['zip_code'])) echo 'display: block;'; ?>"><?php if (!empty($errors['zip_code'])) echo htmlspecialchars($errors['zip_code']); ?></div></div>
                    </div>
                </div>
                
                <!-- Driver's License Section -->
                <div class="address-section">
                    <h3><i class="fas fa-id-card"></i> Driver's License Information (Optional)</h3>
                    <div class="address-grid">
                        <div class="form-group"><label for="license-number">License Number</label><input type="text" id="license-number" name="license_number" value="<?php echo isset($_POST['license_number']) ? htmlspecialchars($_POST['license_number']) : ''; ?>"></div>
                        <div class="form-group"><label for="license-country">Issuing Country</label><select id="license-country" name="license_country"><option value="">Select Country</option><option value="US" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'US') ? 'selected' : ''; ?>>United States</option><option value="UK" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'UK') ? 'selected' : ''; ?>>United Kingdom</option><option value="CA" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'CA') ? 'selected' : ''; ?>>Canada</option><option value="AU" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'AU') ? 'selected' : ''; ?>>Australia</option><option value="LK" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'LK') ? 'selected' : ''; ?>>Sri Lanka</option><option value="IN" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'IN') ? 'selected' : ''; ?>>India</option><option value="JP" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'JP') ? 'selected' : ''; ?>>Japan</option><option value="DE" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'DE') ? 'selected' : ''; ?>>Germany</option><option value="FR" <?php echo (isset($_POST['license_country']) && $_POST['license_country'] == 'FR') ? 'selected' : ''; ?>>France</option></select></div>
                        <div class="form-group"><label for="license-expiry">Expiry Date</label><input type="date" id="license-expiry" name="license_expiry" value="<?php echo isset($_POST['license_expiry']) ? htmlspecialchars($_POST['license_expiry']) : ''; ?>"></div>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="terms-group">
                    <input type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                    <label for="terms">I agree to the <a href="#" onclick="showTerms()">Terms of Service</a> and <a href="#" onclick="showPrivacy()">Privacy Policy</a></label>
                    <div class="error-message" id="terms-error" style="<?php if (!empty($errors['terms'])) echo 'display: block;'; ?>">
                        <?php if (!empty($errors['terms'])) echo htmlspecialchars($errors['terms']); ?>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='login.html'">Back to Login</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Success Modal -->
    <?php if ($success): ?>
    <div id="success-modal" class="success-modal" style="display: block;">
        <div class="success-modal-content">
            <h2><i class="fas fa-check-circle" style="color: #0ef; margin-right: 10px;"></i>Registration Successful!</h2>
            <p>Welcome to WheelBay, <?php echo htmlspecialchars($firstName); ?>. Your account is ready.</p>
           <div class="profile-picture-preview" style="width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden;">
                <?php if (!empty($profileImagePath)): ?>
                    <img src="<?php echo htmlspecialchars($profileImagePath); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Profile Picture">
                <?php else: ?>
                    <i class="fas fa-user" style="font-size: 2em; color: #0ef; margin-top: 15px;"></i>
                <?php endif; ?>
            </div>
            <p>You can now proceed to your login.</p>
            <button class="btn btn-primary" onclick="window.location.href='login.php'">Continue to login</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="wheelbay-footer">
        <p>© 2023 WheelBay. All rights reserved.</p>
        <div class="wheelbay-social-links">
            <a href="#" aria-label="Facebook" onclick="Social()"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Instagram" onclick="Social()"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="Twitter" onclick="Social()"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="LinkedIn" onclick="Social()"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </footer>

    <script>
        // Header scroll effect
        const header = document.querySelector(".wheelbay-header");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                header.classList.add("scrolled");
            } else {
                header.classList.remove("scrolled");
            }
        });

        // Social media link handler
        function Social() {
            alert("This functionality coming soon!");
        }

        // Profile picture upload functionality
        const profileUpload = document.getElementById('profile-upload-preview');
        const profileImageUpload = document.getElementById('profile-image-upload');
        const profilePreviewImage = document.getElementById('profile-preview-image');
        const profileIcon = document.querySelector('#profile-upload-preview i');

        profileUpload.addEventListener('click', () => {
            profileImageUpload.click();
        });

        profileImageUpload.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreviewImage.src = e.target.result;
                    profilePreviewImage.style.display = 'block';
                    profileIcon.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('password-strength-bar');
        const confirmPasswordInput = document.getElementById('confirm-password');
        
        // Password requirement elements
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check for length
            if (password.length >= 8) {
                strength += 1;
                reqLength.classList.remove('invalid');
                reqLength.classList.add('valid');
            } else {
                reqLength.classList.remove('valid');
                reqLength.classList.add('invalid');
            }
            
            // Check for uppercase letters
            if (/[A-Z]/.test(password)) {
                strength += 1;
                reqUppercase.classList.remove('invalid');
                reqUppercase.classList.add('valid');
            } else {
                reqUppercase.classList.remove('valid');
                reqUppercase.classList.add('invalid');
            }
            
            // Check for numbers
            if (/[0-9]/.test(password)) {
                strength += 1;
                reqNumber.classList.remove('invalid');
                reqNumber.classList.add('valid');
            } else {
                reqNumber.classList.remove('valid');
                reqNumber.classList.add('invalid');
            }
            
            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 1;
                reqSpecial.classList.remove('invalid');
                reqSpecial.classList.add('valid');
            } else {
                reqSpecial.classList.remove('valid');
                reqSpecial.classList.add('invalid');
            }
            
            // Update strength bar
            switch(strength) {
                case 0:
                    strengthBar.style.width = '0%';
                    strengthBar.style.backgroundColor = '#ff3333';
                    break;
                case 1:
                    strengthBar.style.width = '25%';
                    strengthBar.style.backgroundColor = '#ff3333';
                    break;
                case 2:
                    strengthBar.style.width = '50%';
                    strengthBar.style.backgroundColor = '#ff9933';
                    break;
                case 3:
                    strengthBar.style.width = '75%';
                    strengthBar.style.backgroundColor = '#33cc33';
                    break;
                case 4:
                    strengthBar.style.width = '100%';
                    strengthBar.style.backgroundColor = '#00cc00';
                    break;
            }
            
            // Validate password match in real-time
            if (confirmPasswordInput.value) {
                validatePasswordMatch();
            }
        });

        // Confirm password validation
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        
        function validatePasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const errorElement = document.getElementById('confirm-password-error');
            
            if (confirmPassword && password !== confirmPassword) {
                confirmPasswordInput.classList.add('input-error');
                errorElement.style.display = 'block';
                return false;
            } else {
                confirmPasswordInput.classList.remove('input-error');
                errorElement.style.display = 'none';
                return true;
            }
        }

        // Form validation
        const form = document.getElementById('register-form');
        
        form.addEventListener('submit', function(e) {
            let isValid = validateForm();
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        function validateForm() {
            let isValid = true;
            
            // Validate first name
            const firstName = document.getElementById('first-name');
            const firstNameError = document.getElementById('first-name-error');
            if (!firstName.value.trim()) {
                firstName.classList.add('input-error');
                firstNameError.style.display = 'block';
                isValid = false;
            } else {
                firstName.classList.remove('input-error');
                firstNameError.style.display = 'none';
            }
            
            // Validate last name
            const lastName = document.getElementById('last-name');
            const lastNameError = document.getElementById('last-name-error');
            if (!lastName.value.trim()) {
                lastName.classList.add('input-error');
                lastNameError.style.display = 'block';
                isValid = false;
            } else {
                lastName.classList.remove('input-error');
                lastNameError.style.display = 'none';
            }
            
            // Validate email
            const email = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('input-error');
                emailError.style.display = 'block';
                isValid = false;
            } else {
                email.classList.remove('input-error');
                emailError.style.display = 'none';
            }
            
            // Validate password
            const password = document.getElementById('password');
            const passwordError = document.getElementById('password-error');
            const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;
            if (!passwordRegex.test(password.value)) {
                password.classList.add('input-error');
                passwordError.style.display = 'block';
                isValid = false;
            } else {
                password.classList.remove('input-error');
                passwordError.style.display = 'none';
            }
            
            // Validate confirm password
            if (!validatePasswordMatch()) {
                isValid = false;
            }
            
            // Validate phone
            const phone = document.getElementById('phone');
            const phoneError = document.getElementById('phone-error');
            const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
            if (!phoneRegex.test(phone.value)) {
                phone.classList.add('input-error');
                phoneError.style.display = 'block';
                isValid = false;
            } else {
                phone.classList.remove('input-error');
                phoneError.style.display = 'none';
            }
            
            // Validate date of birth
            const dob = document.getElementById('dob');
            const dobError = document.getElementById('dob-error');
            if (!dob.value) {
                dob.classList.add('input-error');
                dobError.style.display = 'block';
                isValid = false;
            } else {
                dob.classList.remove('input-error');
                dobError.style.display = 'none';
            }
            
            // Validate address fields
            const addressFields = ['house-no', 'street', 'city', 'state', 'country', 'zip-code'];
            addressFields.forEach(field => {
                const element = document.getElementById(field);
                const errorElement = document.getElementById(`${field}-error`);
                
                if (!element.value.trim() && element.required) {
                    element.classList.add('input-error');
                    errorElement.style.display = 'block';
                    isValid = false;
                } else {
                    element.classList.remove('input-error');
                    errorElement.style.display = 'none';
                }
            });
            
            // Validate terms
            const terms = document.getElementById('terms');
            const termsError = document.getElementById('terms-error');
            if (!terms.checked) {
                termsError.style.display = 'block';
                isValid = false;
            } else {
                termsError.style.display = 'none';
            }
            
            return isValid;
        }

        function redirectToProfile() {
            window.location.href = "profile.php";
        }

        function showTerms() {
            alert("Terms of Service would be displayed here.");
            return false;
        }

        function showPrivacy() {
            alert("Privacy Policy would be displayed here.");
            return false;
        }

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('success-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>