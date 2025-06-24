<?php
// Start a session to access logged-in user data
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

// Set headers to prevent caching
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


// Handle form submission for profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['dob']);
    $houseNo = trim($_POST['house_no']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $zipCode = trim($_POST['zip_code']);
    $licenseNumber = trim($_POST['license_number']);
    $licenseCountry = trim($_POST['license_country']);
    $licenseExpiry = trim($_POST['license_expiry']);
    
    // Initialize profile image path with existing value
    $profileImagePath = $user['profile_image'] ?? null;
    
    // Handle file upload if a new image was provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profile_images/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $fileExt = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('profile_') . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                $profileImagePath = $targetPath;
                
                // Delete old profile image if it exists and is not the default
                if (!empty($user['profile_image']) && $user['profile_image'] !== 'img/default-user.jpg' && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
            }
        }
    }
    
    // Prepare the SQL update statement
    $sql = "UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            email = ?, 
            phone = ?, 
            dob = ?, 
            house_no = ?, 
            street = ?, 
            city = ?, 
            state = ?, 
            country = ?, 
            zip_code = ?, 
            license_number = ?, 
            license_country = ?, 
            license_expiry = ?,
            profile_image = ?,
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("sssssssssssssssi", 
            $firstName, 
            $lastName, 
            $email, 
            $phone, 
            $dob, 
            $houseNo, 
            $street, 
            $city, 
            $state, 
            $country, 
            $zipCode, 
            $licenseNumber, 
            $licenseCountry, 
            $licenseExpiry,
            $profileImagePath,
            $loggedInUserId);
        
        if ($stmt->execute()) {
            // Success - reload the page to show updated data
            header("Location: user.php?success=1");
            exit();
        } else {
            // Error
            $error = "Failed to update profile. Please try again. Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Database error. Please try again. Error: " . $conn->error;
    }
}

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
}

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
    <link rel="stylesheet" href="css/user.css">

</head>
<body class="wheelbay-body">
    <!-- Header with logo and navigation -->
    <header class="wheelbay-header">
        <img class="wheelbay-logo" src="img/Logo01.png" alt="WheelBay Logo">
        <nav>
            <a href="home.php">Home<span></span></a>
            <a href="cars.php">Cars<span></span></a>
            <a href="wishlist.php">Wishlist<span></span></a>
            <a href="about.php">About<span></span></a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout<span></span></a>
            <?php else: ?>
                <a href="login.php">Login<span></span></a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Main content -->
    <main class="wheelbay-main">
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                Profile updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message" style="color: red; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Section -->
        <section class="profile-section">
            <img src="<?php echo $profileImage; ?>" alt="Profile Picture" class="profile-picture" id="profile-picture">
            <input type="file" id="profile-image-input" accept="image/*">
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
                    <input type="text" id="edit-first-name" name="first_name" value="<?php echo $firstName; ?>" required>
                </div>
                <div class="modal-form-group">
                    <label for="edit-last-name">Last Name</label>
                    <input type="text" id="edit-last-name" name="last_name" value="<?php echo $lastName; ?>" required>
                </div>
                <div class="modal-form-group">
                    <label for="edit-email">Email</label>
                    <input type="email" id="edit-email" name="email" value="<?php echo $email; ?>" required>
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