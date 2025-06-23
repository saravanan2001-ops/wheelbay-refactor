<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include the database connection file
require_once 'db_conn.php'; // Adjust path if necessary

// Define the directory where uploaded images will be stored
$uploadDir = 'uploads/profile_images/'; // Ensure this directory exists and is writable!
// Create the upload directory if it doesn't exist
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die("Failed to create upload directory");
    }
}
// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get and Sanitize Input Data
    // Use $conn->real_escape_string or prepared statements for safety
    // Prepared statements are generally preferred. Let's use prepared statements.

    // Check if required fields are set and not empty
    $required_fields = ['fullName', 'phoneNumber', 'address', 'userType'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            // Handle missing or empty required fields
            // You might want to redirect back to the form with an error message
            die("Error: Missing or empty required field: " . htmlspecialchars($field));
        }
    }

    // Assign sanitized variables
    $fullName = trim($_POST['fullName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $address = trim($_POST['address']);
    $userType = strtolower(trim($_POST['userType'])); // Convert to lowercase for consistency

    // Basic validation for userType
    if ($userType !== 'seller' && $userType !== 'buyer') {
        die("Error: Invalid user type specified.");
    }

    // 2. Handle File Upload (Profile Image)
    $profileImagePath = null; // Initialize path variable

    // Check if file was uploaded without errors
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {

        $fileTmpPath = $_FILES['profileImage']['tmp_name'];
        $fileName = $_FILES['profileImage']['name'];
        $fileSize = $_FILES['profileImage']['size'];
        $fileType = $_FILES['profileImage']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Define allowed file extensions
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        // Basic validation
        if (!in_array($fileExtension, $allowedfileExtensions)) {
            die("Error: Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.");
        }

        // You might also want to check file size: $_FILES['profileImage']['size']
        // E.g., if ($fileSize > 5000000) { die("File too large."); } // 5MB limit

        // Generate a unique file name to prevent overwriting and security issues
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Using md5 and time

        // Define the full path where the file will be moved
        $destPath = $uploadDir . $newFileName;

        // Move the uploaded file from the temporary directory to the target directory
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // File uploaded successfully, store the relative path in the database
            $profileImagePath = $destPath;
        } else {
            // Handle file move error
            // Log the error for debugging
            error_log("Error moving uploaded file from " . $fileTmpPath . " to " . $destPath);
            die("Error: There was a problem uploading your profile image. Please try again.");
        }

    } else {
        // Handle file upload error or missing file if required
        // If the HTML input has 'required', the browser should handle it, but server-side check is safer
        if ($_FILES['profileImage']['error'] !== UPLOAD_ERR_NO_FILE) {
             // An error occurred other than no file being sent
             $errorCode = $_FILES['profileImage']['error'];
             $errorMessage = 'Unknown upload error.';
             switch ($errorCode) {
                 case UPLOAD_ERR_INI_SIZE:
                 case UPLOAD_ERR_FORM_SIZE:
                     $errorMessage = 'Uploaded file is too large.';
                     break;
                 case UPLOAD_ERR_PARTIAL:
                     $errorMessage = 'File upload was incomplete.';
                     break;
                 case UPLOAD_ERR_NO_FILE:
                     // This case is handled by the outer if/else structure, but included for completeness
                     $errorMessage = 'No file was uploaded.';
                     break;
                 case UPLOAD_ERR_NO_TMP_DIR:
                     $errorMessage = 'Missing a temporary folder.';
                     break;
                 case UPLOAD_ERR_CANT_WRITE:
                     $errorMessage = 'Failed to write file to disk.';
                     break;
                 case UPLOAD_ERR_EXTENSION:
                     $errorMessage = 'A PHP extension stopped the file upload.';
                     break;
             }
             error_log("Profile image upload error: " . $errorMessage . " (Code: " . $errorCode . ")");
             die("Error: Profile image upload failed: " . $errorMessage);
        } else {
             // No file was uploaded, but the HTML had 'required'.
             // You might want to handle this specifically if it's truly required.
             // If profile image is optional, remove the 'required' attribute from HTML
             // and you don't need this else block for UPLOAD_ERR_NO_FILE.
             die("Error: Profile image is required.");
        }
    }


    // 3. Insert Data into Database using Prepared Statements
    $sql = "INSERT INTO users (full_name, phone_number, address, profile_image_path, user_type) VALUES (?, ?, ?, ?, ?)";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    // Check if the statement preparation was successful
    if ($stmt === false) {
        // Log the error instead of displaying it publicly
        error_log("MySQL prepare error: " . $conn->error);
        die("Sorry, there was a problem processing your registration.");
    }

    // Bind parameters to the prepared statement
    // 'sssss' indicates that all 5 parameters are strings
    $bindResult = $stmt->bind_param("sssss", $fullName, $phoneNumber, $address, $profileImagePath, $userType);

    if ($bindResult === false) {
         error_log("MySQL bind_param error: " . $stmt->error);
         die("Sorry, there was a problem preparing registration data.");
    }


    // Execute the prepared statement
    if ($stmt->execute()) {
        // Registration successful

        // 4. Handle Success - Redirect based on user type
        // Get the ID of the newly inserted user
        $newUserId = $conn->insert_id;
        echo "Registration successful! User ID: " . $newUserId; // Or redirect

        // Redirect the user. Note: Server-side redirect is generally cleaner
        // than relying solely on client-side JS for post-processing redirects.
        // Ensure no output is sent before header().
        if ($userType === 'seller') {
            header("Location: ../dealer.php?user_id=" . $newUserId); // Pass ID if needed
            $stmt->close();
            $conn->close();
            exit(); // Stop further script execution after redirect
        } else { // buyer
            header("Location: ../user.php?user_id=" . $newUserId); // Pass ID if needed
            $stmt->close();
            $conn->close();
            exit(); // Stop further script execution after redirect
        }

    } else {
        // 5. Handle Failure
        // Log the error instead of displaying it publicly
        error_log("MySQL execute error: " . $stmt->error);
        // Optional: Delete the uploaded image if database insert failed
        if ($profileImagePath && file_exists($profileImagePath)) {
             unlink($profileImagePath);
        }
        $stmt->close();
        $conn->close();
        die("Error during registration. Please try again."); // Generic error for user
    }

} else {
    // If someone tries to access this script directly via GET request
    die("Invalid request method.");
}
?>