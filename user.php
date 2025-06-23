<?php
// Start a session to access logged-in user data
session_start();

// Check if user is logged in
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Include the database connection file
require_once 'php/db_conn.php';

// Get the logged-in user's ID from the session
$loggedInUserId = $_SESSION['user_id'];

// Initialize variables for user data
$firstName = "User";
$lastName = "Profile";
$fullName = "User Profile";
$email = "N/A";
$location = "N/A"; // Will combine city/state/country
$profileImage = "img/default-user.jpg"; // Default image
$phone = "N/A";
$dob = "N/A";
$licenseNumber = "N/A";
$licenseCountry = "N/A";
$licenseExpiry = "N/A";
$userType = "Online";
$rawDob = ''; // To populate the date input in the edit modal
$rawLicenseExpiry = ''; // To populate the date input in the edit modal

// Fetch User Data from the Database
$sql = "SELECT first_name, last_name, email, phone, dob, house_no, street, city, state, country, zip_code, license_number, license_country, license_expiry, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $loggedInUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // User data found, assign to variables
        $firstName = htmlspecialchars($user['first_name']);
        $lastName = htmlspecialchars($user['last_name']);
        $fullName = $firstName . ' ' . $lastName;
        $email = htmlspecialchars($user['email']);

}

        // Combine address parts for general location display
        $locationParts = [];
        if (!empty($user['city'])) $locationParts[] = htmlspecialchars($user['city']);
        if (!empty($user['state'])) $locationParts[] = htmlspecialchars($user['state']);
        if (!empty($user['country'])) $locationParts[] = htmlspecialchars($user['country']);
        $location = implode(', ', $locationParts);

        
        // Set default image path
        $profileImage = "img/default-user.jpg";

        // Check if a profile image path exists in the database and the file exists
        if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
            $profileImage = $user['profile_image'];
        }

        // Other details for the Personal Information section
        $phone = !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'N/A';
        $dob = !empty($user['dob']) ? htmlspecialchars($user['dob']) : 'N/A';
        // Format DOB for display
        $displayDob = $dob;
        if ($dob !== 'N/A' && $dobDateObj = DateTime::createFromFormat('Y-m-d', $dob)) {
             $displayDob = $dobDateObj->format('F j, Y'); // e.g., "March 15, 2001"
             $rawDob = $user['dob']; // Keep YYYY-MM-DD for input value
        } else {
             $rawDob = '';
        }

        $licenseNumber = !empty($user['license_number']) ? htmlspecialchars($user['license_number']) : 'N/A';
        $licenseCountry = !empty($user['license_country']) ? htmlspecialchars($user['license_country']) : 'N/A';
        $licenseExpiry = !empty($user['license_expiry']) ? htmlspecialchars($user['license_expiry']) : 'N/A';
        // Format License Expiry for display
        $displayLicenseExpiry = $licenseExpiry;
        if ($licenseExpiry !== 'N/A' && $licenseExpiryDateObj = DateTime::createFromFormat('Y-m-d', $licenseExpiry)) {
             $displayLicenseExpiry = $licenseExpiryDateObj->format('F j, Y'); // e.g., "December 31, 2025"
             $rawLicenseExpiry = $user['license_expiry']; // Keep YYYY-MM-DD for input value
        } else {
             $rawLicenseExpiry = '';
        }
        $displayLicense = $licenseNumber . (!empty($licenseCountry) && $licenseCountry !== 'N/A' ? ' (' . $licenseCountry . ')' : '');
        if ($displayLicense === 'N/A') $displayLicense = 'Not Provided';

    } else {
        // This case should ideally not happen if session is valid, but handle defensively
        error_log("User ID " . $loggedInUserId . " found in session but not in database or wrong type.");
        // Redirect to login, destroying session
        session_unset();
        session_destroy();
        header("Location: login.php?error=user_data_missing");
        exit();
    }
    $stmt->close();
 

$conn->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $fullName; ?> - Profile | WheelBay</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
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
            padding: 40px;
            margin-top: 100px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Profile Section */
        .profile-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-picture {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #0ef;
            margin-bottom: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(0, 238, 255, 0.3);
        }

        .profile-picture:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 238, 255, 0.5);
        }

        .profile-info h2 {
            font-size: 2.2em;
            margin-bottom: 10px;
            color: #0ef;
        }

        .profile-info p {
            font-size: 1.1em;
            color: #ccc;
            margin-bottom: 5px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 25px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 25px;
            border-radius: 10px;
            min-width: 120px;
            transition: transform 0.3s, background 0.3s;
            cursor: pointer;
        }

        .stat-item:hover {
            background: rgba(0, 238, 255, 0.2);
            transform: translateY(-5px);
        }

        .stat-item i {
            font-size: 1.8em;
            color: #0ef;
            margin-bottom: 10px;
        }

        .stat-item .stat-number {
            font-size: 1.5em;
            font-weight: 600;
            color: white;
        }

        .stat-item .stat-label {
            font-size: 0.9em;
            color: #aaa;
        }

        /* Profile Details Section */
        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .detail-card {
            background: #1f1f1f;
            border-radius: 15px;
            padding: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .detail-card h3 {
            color: #0ef;
            margin-bottom: 20px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-card h3 i {
            font-size: 1.2em;
        }

        .detail-item {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .detail-item i {
            color: #0ef;
            margin-right: 15px;
            margin-top: 3px;
            font-size: 1.1em;
        }

        .detail-item .detail-label {
            color: #aaa;
            font-size: 0.95em;
            margin-bottom: 3px;
        }

        .detail-item .detail-value {
            color: white;
            font-size: 1.05em;
        }

        /* Edit Profile Button */
        .edit-profile-btn {
            background: linear-gradient(45deg, #00d2ff, #0ef);
            color: black;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            margin-top: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
            margin-right: auto;
        }

        .edit-profile-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 238, 255, 0.4);
        }

        /* Recent Activity */
        .activity-list {
            margin-top: 20px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #333;
            transition: all 0.3s;
        }

        .activity-item:hover {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 12px 15px;
            margin: 0 -15px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: rgba(0, 238, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #0ef;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: white;
            font-size: 0.95em;
        }

        .activity-time {
            color: #aaa;
            font-size: 0.8em;
            margin-top: 3px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }

        .modal-content {
            background-color: #1f1f1f;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            max-height: 80vh; /* Limit height to 80% of viewport */
            overflow-y: auto; /* Enable vertical scrolling */
            position: relative;
            animation: modalopen 0.5s;
        }

        .modal-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            min-height: min-content; /* Ensure form can grow as needed */
        }
        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .close-modal {
            position: absolute;
            right: 25px;
            top: 15px;
            font-size: 28px;
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #0ef;
        }

        .modal h2 {
            color: #0ef;
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-form-group {
            margin-bottom: 15px;
        }

        .modal-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0ef;
        }

        .modal-form-group input,
        .modal-form-group select,
        .modal-form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: none;
            background-color: #262626;
            color: white;
            font-size: 1em;
        }

        .modal-form-group input:focus,
        .modal-form-group select:focus,
        .modal-form-group textarea:focus {
            outline: none;
            box-shadow: 0 0 0 2px #0ef;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .modal-btn-primary {
            background: #0ef;
            color: black;
            border: none;
            font-weight: 600;
        }

        .modal-btn-primary:hover {
            background: #0bd6c2;
        }

        .modal-btn-secondary {
            background: transparent;
            color: #0ef;
            border: 1px solid #0ef;
        }

        .modal-btn-secondary:hover {
            background: rgba(0, 238, 255, 0.1);
        }

        /* Verification Badge */
        .verification-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(0, 238, 255, 0.1);
            color: #0ef;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            margin-left: 10px;
        }

        .verification-badge i {
            margin-right: 5px;
        }

        /* Hidden file input */
        #profile-image-input {
            display: none;
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
            
            .profile-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .profile-details {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 90%;
                margin: 10% auto; /* Reduced from 20% to give more space */
                max-height: 85vh; /* Slightly more height on mobile */
            }
        }
        .wheelbay-footer {
            background-color: #1f1f1f;
            color: white;
            text-align: center;
            padding: 40px 20px;
            margin-top: 40px;
        }

        .wheelbay-footer p {
            margin: 0;
        }

        /* Specific CSS for social links */
        .wheelbay-social-links {
            margin-top: 20px;
        }

        .wheelbay-social-links a {
            margin: 0 10px;
            font-size: 1.5em;
            color: #17fee3;
            transition: color 0.3s;
        }

        .wheelbay-social-links a:hover {
            color: #14d3c3;
        }

        .wheelbay-social-links img {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }

        .wheelbay-social-links a:hover img {
            transform: scale(1.2);
        }
        /* Three-column layout for profile details */
        .profile-details-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .info-section {
            background: #1f1f1f;
            border-radius: 15px;
            padding: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .info-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .info-section h3 {
            color: #0ef;
            margin-bottom: 20px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        
        .info-section h3 i {
            font-size: 1.2em;
        }
        
        .info-item {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-item i {
            color: #0ef;
            margin-right: 15px;
            margin-top: 3px;
            font-size: 1.1em;
            min-width: 20px;
        }
        
        .info-item .info-label {
            color: #aaa;
            font-size: 0.95em;
            margin-bottom: 3px;
        }
        
        .info-item .info-value {
            color: white;
            font-size: 1.05em;
            word-break: break-word;
        }
        
        .not-provided {
            color: #777;
            font-style: italic;
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
            <a href="wishlist.html">Wishlist<span></span></a>
            <a href="about.html">About<span></span></a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout<span></span></a>
            <?php else: ?>
                <a href="login.php">Login<span></span></a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Main content -->
    <main class="wheelbay-main">
        <!-- Profile Section -->
        <section class="profile-section">
            <img src="<?php echo $profileImage; ?>" alt="Profile Picture" class="profile-picture" id="profile-picture">
            <input type="file" id="profile-image-input" accept="uploads/profile_images/**">
            <div class="profile-info">
                <h2><?php echo $fullName; ?> <span class="verification-badge"><i class="fas fa-check-circle"></i><?php echo $userType; ?></span></h2>
                <p><i class="fas fa-envelope"></i> <?php echo $email; ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo $location; ?></p>
            </div>

            <!-- Profile Stats -->
            <div class="profile-stats">
                <div class="stat-item" onclick="viewSection('viewed')">
                    <i class="fas fa-car"></i>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Cars Viewed</div>
                </div>
                <div class="stat-item" onclick="viewSection('wishlist')">
                    <i class="fas fa-heart"></i>
                    <div class="stat-number">5</div>
                    <div class="stat-label">Wishlist</div>
                </div>
                <div class="stat-item" onclick="viewSection('reviews')">
                    <i class="fas fa-star"></i>
                    <div class="stat-number">8</div>
                    <div class="stat-label">Reviews</div>
                </div>
            </div>
        </section>

        <!-- Profile Details Sections -->
        <div class="profile-details-container">
            <!-- Personal Information -->
            <div class="info-section">
                <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="info-label">First Name</div>
                        <div class="info-value"><?php echo $firstName ?: '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="info-label">Last Name</div>
                        <div class="info-value"><?php echo $lastName ?: '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo $email ?: '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo $phone ?: '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-birthday-cake"></i>
                    <div>
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo $displayDob !== 'N/A' ? $displayDob : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="info-section">
                <h3><i class="fas fa-home"></i> Address Information</h3>
                
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <div class="info-label">House/Unit Number</div>
                        <div class="info-value"><?php echo !empty($user['house_no']) ? htmlspecialchars($user['house_no']) : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-road"></i>
                    <div>
                        <div class="info-label">Street</div>
                        <div class="info-value"><?php echo !empty($user['street']) ? htmlspecialchars($user['street']) : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-city"></i>
                    <div>
                        <div class="info-label">City</div>
                        <div class="info-value"><?php echo !empty($user['city']) ? htmlspecialchars($user['city']) : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-building"></i>
                    <div>
                        <div class="info-label">State/Province</div>
                        <div class="info-value"><?php echo !empty($user['state']) ? htmlspecialchars($user['state']) : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-globe"></i>
                    <div>
                        <div class="info-label">Country</div>
                        <div class="info-value"><?php echo !empty($user['country']) ? htmlspecialchars($user['country']) : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-mail-bulk"></i>
                    <div>
                        <div class="info-label">Zip/Postal Code</div>
                        <div class="info-value"><?php echo !empty($user['zip_code']) ? htmlspecialchars($user['zip_code']) : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
            </div>

            <!-- License Information -->
            <div class="info-section">
                <h3><i class="fas fa-id-card"></i> License Information</h3>
                
                <div class="info-item">
                    <i class="fas fa-id-card"></i>
                    <div>
                        <div class="info-label">License Number</div>
                        <div class="info-value"><?php echo $licenseNumber !== 'N/A' ? $licenseNumber : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-globe"></i>
                    <div>
                        <div class="info-label">Issuing Country</div>
                        <div class="info-value"><?php echo $licenseCountry !== 'N/A' ? $licenseCountry : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-calendar-times"></i>
                    <div>
                        <div class="info-label">Expiry Date</div>
                        <div class="info-value"><?php echo $displayLicenseExpiry !== 'N/A' ? $displayLicenseExpiry : '<span class="not-provided">Not provided</span>'; ?></div>
                    </div>
                </div>

            </div>
        </div>


        <!-- Edit Profile Button -->
        <button class="edit-profile-btn" onclick="openEditModal()">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
    </main>

    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">×</span>
            <h2>Edit Profile</h2>
            <form class="modal-form" id="profile-edit-form" method="post" action="user.php" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?php echo $loggedInUserId; ?>">

                <div class="modal-form-group">
                    <label for="edit-first-name">First Name</label>
                    <input type="text" id="edit-first-name" name="first_name" value="<?php echo $firstName; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-last-name">Last Name</label>
                    <input type="text" id="edit-last-name" name="last_name" value="<?php echo $lastName; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-email">Email</label>
                    <input type="email" id="edit-email" name="email" value="<?php echo $email; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-phone">Phone</label>
                    <input type="tel" id="edit-phone" name="phone" value="<?php echo $phone !== 'N/A' ? $phone : ''; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-dob">Date of Birth</label>
                    <input type="date" id="edit-dob" name="dob" value="<?php echo $rawDob; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-house-no">House/Unit Number</label>
                    <input type="text" id="edit-house-no" name="house_no" value="<?php echo htmlspecialchars($user['house_no'] ?? ''); ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-street">Street</label>
                    <input type="text" id="edit-street" name="street" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-city">City</label>
                    <input type="text" id="edit-city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-state">State/Province</label>
                    <input type="text" id="edit-state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-country">Country</label>
                    <input type="text" id="edit-country" name="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-zip-code">Zip/Postal Code</label>
                    <input type="text" id="edit-zip-code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-license-number">License Number (Optional)</label>
                    <input type="text" id="edit-license-number" name="license_number" value="<?php echo $licenseNumber !== 'N/A' ? $licenseNumber : ''; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-license-country">Issuing Country (Optional)</label>
                    <input type="text" id="edit-license-country" name="license_country" value="<?php echo $licenseCountry !== 'N/A' ? $licenseCountry : ''; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-license-expiry">Expiry Date (Optional)</label>
                    <input type="date" id="edit-license-expiry" name="license_expiry" value="<?php echo $rawLicenseExpiry; ?>">
                </div>
                <div class="modal-form-group">
                    <label for="edit-profile-image">Change Profile Picture (Optional)</label>
                    <input type="file" id="edit-profile-image" name="profile_image" accept="image/*">
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

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

        // Profile Image Change Functionality
        const profilePicture = document.getElementById('profile-picture');
        const profileImageInput = document.getElementById('profile-image-input');
        const editProfileImageInput = document.getElementById('edit-profile-image');

        profilePicture.addEventListener('click', () => {
            openEditModal();
        });

        profileImageInput.addEventListener('change', (event) => {
             const file = event.target.files[0];
             if (file) {
                 const reader = new FileReader();
                 reader.onload = (e) => {
                     profilePicture.src = e.target.result;
                     alert("Profile picture preview updated. Save changes in the modal!");
                 };
                 reader.readAsDataURL(file);
             }
        });

        if (editProfileImageInput) {
            editProfileImageInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        profilePicture.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // View different sections when clicking stats
        function viewSection(section) {
            alert(`Viewing your ${section} section. In a real app, this would navigate to that section or load content dynamically.`);
        }

        // Modal functionality
        const modal = document.getElementById('edit-profile-modal');
        const profileEditForm = document.getElementById('profile-edit-form');

        function openEditModal() {
             modal.style.display = 'block';
        }

        function closeEditModal() {
            modal.style.display = 'none';
             profileEditForm.reset();
        }

        // Close modal when clicking outside of it
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeEditModal();
            }
        });

        // Enable two-factor authentication
        function enableTwoFactorAuth() {
            const enable = confirm("Would you like to enable two-factor authentication for added security?");
            if (enable) {
                const btn = document.querySelector(".enable-2fa");
                if (btn) {
                     btn.textContent = "Enabled";
                     btn.style.backgroundColor = '#0ef';
                     btn.disabled = true;
                }
                const securityValueDiv = document.querySelector(".detail-card:nth-of-type(2) .detail-item:nth-of-type(5) .detail-value");
                 if (securityValueDiv) {
                    securityValueDiv.innerHTML = "2FA Enabled <span style='color:#0ef'>✓</span>";
                 }
                alert("Two-factor authentication has been enabled (client-side visual only). Implement server-side setup!");
            }
        }

        // Make activity items clickable
        document.querySelectorAll('.activity-item').forEach(item => {
            item.addEventListener('click', function() {
                const activityText = this.querySelector('.activity-text').textContent;
                alert(`Viewing details for: "${activityText}". This requires navigating to another page or loading content.`);
            });
        });

        // Client-side preview for the file input in the edit modal
        const editProfileImagePreview = document.getElementById('profile-picture');
        if (editProfileImageInput) {
            editProfileImageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        editProfileImagePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html>