<?php
// Include the database connection file
// Make sure the path is correct relative to this dealer.php file
require_once 'php/db_conn.php';

// 1. Get the user_id from the URL
// Use intval() to ensure it's treated as an integer and prevent basic injection
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Check if userId is valid
if ($userId <= 0) {
    // Handle the case where user_id is missing or invalid
    die("Error: User ID not specified or invalid.");
    // In a real application, you might redirect to a login page or an error page
}

// Initialize variables for dealer data
$dealerName = "Dealer Not Found";
$dealerProfileImage = "img/default-dealer.jpg"; // Provide a default image path
$dealerRegistrationDate = "N/A";
$dealerLocation = "N/A";
$dealerStatus = "N/A"; // You might want to add a status field for dealers
$dealerEmail = "N/A"; // Assuming email might be added later
$carsInStock = 0;
$totalSales = 0; // These stats require more complex queries or tables, keeping static for now
$customerRating = "N/A"; // Requires a rating system, keeping static for now

$inventory = []; // Array to hold inventory data

// 2. Fetch Dealer Profile Data
$sqlDealer = "SELECT full_name, profile_image_path, registration_date, address, user_type FROM dealers WHERE id = ? AND user_type = 'seller'";
$stmtDealer = $conn->prepare($sqlDealer);

if ($stmtDealer) {
    $stmtDealer->bind_param("i", $userId);
    $stmtDealer->execute();
    $resultDealer = $stmtDealer->get_result();

    if ($dealer = $resultDealer->fetch_assoc()) {
        // Dealer found, assign fetched data
        $dealerName = htmlspecialchars($dealer['full_name']);
        // Use the fetched image path if it exists and the file exists, otherwise use default
        $uploadedImagePath = $dealer['profile_image_path'];
        if ($uploadedImagePath && file_exists($uploadedImagePath)) {
             $dealerProfileImage = htmlspecialchars($uploadedImagePath);
        }
        // Format registration date
        $date = new DateTime($dealer['registration_date']);
        $dealerRegistrationDate = $date->format('F Y'); // e.g., "March 2018"
        $dealerLocation = htmlspecialchars($dealer['address']); // Display full address for now
        // $dealerEmail = htmlspecialchars($dealer['email']); // Uncomment if email is added to table
        // Check user_type just in case, though the query filters for 'seller'
        if ($dealer['user_type'] !== 'seller') {
             // This should not happen if the query is correct, but good defensive check
             die("Error: User is not a seller profile.");
        }

        // 3. Fetch Dealer Inventory
        $sqlInventory = "SELECT car_id, name, price, status, image_path, views FROM inventory WHERE dealer_id = ?";
        $stmtInventory = $conn->prepare($sqlInventory);

        if ($stmtInventory) {
            $stmtInventory->bind_param("i", $userId);
            $stmtInventory->execute();
            $resultInventory = $stmtInventory->get_result();

            // Fetch all inventory items into an array
            while ($car = $resultInventory->fetch_assoc()) {
                $inventory[] = $car;
            }
            $stmtInventory->close();

             // Optional: Update Cars in Stock stat based on fetched inventory
            $carsInStock = count($inventory);

        } else {
            error_log("Inventory query preparation failed: " . $conn->error);
             // Inventory will remain empty array
        }

    } else {
        // No dealer found with that ID or user_type is not 'seller'
        // Keep default values or show an error message on the page
         $dealerName = "Dealer Profile Not Available";
         // You might redirect here if you strictly require a valid user ID
         // header("Location: error_page.html"); exit();
    }
    $stmtDealer->close();

} else {
    error_log("Dealer query preparation failed: " . $conn->error);
    // Keep default values
}

$conn->close(); // Close the database connection

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($dealerName); ?> - Dealer Profile</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="css/dealer.css">
</head>
<body class="wheelbay-body">
    <!-- Header with logo and navigation -->
    <header class="wheelbay-header">
        <img class="wheelbay-logo" src="css/img/Logo01.png" alt="WheelBay Logo">
        <nav>
            <a href="home.html">Home<span></span></a>
            <a href="cars.html">Cars<span></span></a>
            <a href="wishlist.html">Wishlist<span></span></a>
            <a href="about.html">About<span></span></a>
            <a href="login.html">Logout<span></span></a>
        </nav>
    </header>

    <!-- Main content -->
    <main class="wheelbay-main">
        <section class="profile-section">
            <!-- PHP: Display fetched profile image -->
            <img src="<?php echo $dealerProfileImage; ?>" alt="Dealer Profile Picture" class="profile-picture" id="profile-picture">
            <!-- File input for changing image (requires separate PHP handling) -->
            <input type="file" id="profile-image-input" accept="image/*">
            <div class="profile-info">
                <!-- PHP: Display fetched dealer name -->
                <h2><?php echo $dealerName; ?> <span class="verified-badge">Verified</span></h2>
                <!-- PHP: Display fetched dealer email (if added to table) -->
                <p><?php echo $dealerEmail; ?></p>
                <!-- PHP: Display formatted registration date -->
                <p>Dealer since: <?php echo $dealerRegistrationDate; ?></p>
                 <!-- PHP: Display fetched location -->
                <p>Location: <?php echo htmlspecialchars($dealerLocation); ?></p>

                <div class="dealer-stats">
                    <div class="stat-box">
                        <!-- PHP: Display fetched cars in stock count -->
                        <h3><?php echo $carsInStock; ?></h3>
                        <p>Cars in Stock</p>
                    </div>
                    <div class="stat-box">
                         <!-- Static stat (requires sales tracking in DB) -->
                        <h3>128</h3>
                        <p>Total Sales</p>
                    </div>
                    <div class="stat-box">
                         <!-- Static stat (requires rating system in DB) -->
                        <h3>4.9</h3>
                        <p>Customer Rating</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="inventory-actions">
            <!-- You might want to pass the user ID here too -->
            <button class="action-button" onclick="addNewCar(<?php echo $userId; ?>)">
                <ion-icon name="add-outline"></ion-icon> Add New Vehicle
            </button>
        </div>

        <section class="inventory-section">
            <h3>Your Inventory</h3>

            <div class="inventory-list">
                <?php
                // 4. Loop through fetched inventory and display each car card
                if (!empty($inventory)) {
                    foreach ($inventory as $car) {
                        // Use htmlspecialchars() for safety when displaying data
                        $carId = htmlspecialchars($car['car_id']);
                        $carName = htmlspecialchars($car['name']);
                        $carPrice = number_format($car['price'], 2); // Format price
                        $carStatus = htmlspecialchars(ucfirst($car['status'])); // Capitalize status
                        $carViews = htmlspecialchars($car['views']);
                        // Use the fetched image path if it exists and the file exists, otherwise use default
                        $carImagePath = htmlspecialchars($car['image_path']);
                        if (empty($carImagePath) || !file_exists($carImagePath)) {
                            $carImagePath = "img/default-car.jpg"; // Provide a default car image path
                        }
                ?>
                <div class="inventory-card">
                    <!-- PHP: Display car image -->
                    <img src="<?php echo $carImagePath; ?>" alt="<?php echo $carName; ?>">
                    <!-- PHP: Display car name -->
                    <h4><?php echo $carName; ?></h4>
                    <!-- PHP: Display car details -->
                    <p>Price: $<?php echo $carPrice; ?></p>
                    <p>Status: <?php echo $carStatus; ?></p>
                    <p>Views: <?php echo $carViews; ?></p>
                    <div class="inventory-actions-buttons">
                        <!-- Pass carId to JS functions for editing/deleting -->
                        <button class="inventory-action-button edit-button" onclick="editCar(<?php echo $carId; ?>)">
                            <ion-icon name="create-outline"></ion-icon> Edit
                        </button>
                        <button class="inventory-action-button delete-button" onclick="deleteCar(<?php echo $carId; ?>, this)">
                            <ion-icon name="trash-outline"></ion-icon> Delete
                        </button>
                    </div>
                </div>
                <?php
                    } // End foreach loop
                } else {
                    // Display a message if no inventory is found
                    echo "<p style='text-align: center; grid-column: 1 / -1;'>No vehicles in inventory.</p>";
                }
                ?>
            </div>
        </section>
    </main>

    <footer class="wheelbay-footer">
        <p>© 2023 WheelBay. All rights reserved.</p>
        <div class="wheelbay-social-links">
            <a href="#" aria-label="Facebook" onclick="Social()"><ion-icon name="logo-facebook"></ion-icon></a>
            <a href="#" aria-label="Instagram" onclick="Social()"><ion-icon name="logo-instagram"></ion-icon></a>
            <a href="#" aria-label="Whatsapp" onclick="Social()"><ion-icon name="logo-whatsapp"></ion-icon></a>
        </div>
    </footer>

    <script>
        // Existing JS for header scroll effect and social links
        const header = document.querySelector(".wheelbay-header");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                header.classList.add("scrolled");
            } else {
                header.classList.remove("scrolled");
            }
        });

        function Social() {
            alert("This functionality coming soon!");
        }

        // Profile Image Change Functionality (Client-side only)
        // To make this persistent, you'd need PHP to handle the file upload
        // when the image is selected and save the new path to the database.
        const profilePicture = document.getElementById('profile-picture');
        const profileImageInput = document.getElementById('profile-image-input');

        profilePicture.addEventListener('click', () => {
            profileImageInput.click(); // Trigger file input dialog
        });

        profileImageInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    profilePicture.src = e.target.result; // Update profile picture temporarily
                    // !!! IMPORTANT: To save this change permanently, you need to
                    // send this file to a PHP script using fetch/XMLHttpRequest
                    // which will handle the server-side upload and database update.
                    // This current JS only changes it in the browser session.
                    alert("Profile image updated locally. Click SAVE or implement server-side upload.");
                };
                reader.readAsDataURL(file);
            }
        });

        // Dealer Functions - Now passing IDs
        // Pass the user ID from PHP into the JS function calls if needed for the target page
        function addNewCar(dealerId) {
             // Redirect to a page to add a new car, potentially passing the dealer ID
             window.location.href = "registercar.php?dealer_id=" + dealerId;
        }

        function editCar(carId) {
            alert(`Editing details for car ID: ${carId}`);
            // In a real application, this would redirect to an edit form:
            // window.location.href = "editcar.php?car_id=" + carId;
        }

        // Modified deleteCar to accept carId and the button element
        function deleteCar(carId, buttonElement) {
            if (confirm(`Are you sure you want to delete car ID: ${carId} from your inventory?`)) {
                // In a real application, you would send an AJAX request (fetch or XHR) to a PHP script
                // (e.g., 'delete_car.php') passing the carId.
                // If the PHP script confirms successful deletion, then remove the element from the DOM.

                alert(`Sending request to delete car ID: ${carId}`);

                // Example of how you MIGHT use fetch (requires a delete_car.php script)
                /*
                fetch('delete_car.php', {
                    method: 'POST', // or 'GET' depending on your delete script
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded', // or 'application/json'
                    },
                    body: 'car_id=' + carId // Send data
                })
                .then(response => response.json()) // Assuming your PHP returns JSON
                .then(data => {
                    if (data.success) {
                        // Remove the card from the DOM on success
                        const card = buttonElement.closest('.inventory-card');
                        card.remove();
                        alert(`Car ID: ${carId} has been removed from your inventory.`);
                        // You might also update the 'Cars in Stock' count here
                    } else {
                        alert('Error deleting car: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while trying to delete the car.');
                });
                */

                // For now, we'll just remove it from the DOM visually as in your original code
                const card = buttonElement.closest('.inventory-card');
                card.remove();
                alert(`Car ID: ${carId} has been removed (visually). Implement server-side deletion.`);
                // You should also update the 'Cars in Stock' count visually if you remove it here
            }
        }
    </script>
</body>
</html>