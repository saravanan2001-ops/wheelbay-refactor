<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all cars in user's wishlist
    $stmt = $conn->prepare("
        SELECT c.* 
        FROM cars c
        JOIN wishlist w ON c.id = w.car_id
        WHERE w.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlistCars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $wishlistCars = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | WheelBay</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="css/cars.css">
    <style>
        .wheelbay-card {
            display: block;
            transition: all 0.3s ease;
        }
        .no-cars-message, .error-message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 2rem;
            font-size: 1.2rem;
            color: #ff5555;
        }
        .car-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }
        .wishlist-empty {
            text-align: center;
            padding: 4rem;
            font-size: 1.5rem;
        }
        .wishlist-empty a {
            color: #00eeff;
            text-decoration: none;
            font-weight: bold;
        }
        .wishlist-empty a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="wheelbay-body">
    <header class="wheelbay-header">
        <img class="wheelbay-logo" src="img/Logo01.png" alt="WheelBay Logo">
        <nav>
            <a href="home.php">Home<span></span></a>
            <a href="cars.php">Cars<span></span></a>
            <a href="wishlist.php">Wishlist<span></span></a>
            <a href="about.php">About<span></span></a>
            <a href="logout.php">Logout<span></span></a>
        </nav>
    </header>

    <main class="wheelbay-main">
        <h2 class="wheelbay-heading">My Wishlist</h2>
        <div class="wheelbay-car-list" id="car-list">
            <?php if (isset($error)): ?>
                <div class="error-message">Database Error: <?php echo htmlspecialchars($error); ?></div>
            <?php elseif (empty($wishlistCars)): ?>
                <div class="wishlist-empty">
                    <p>Your wishlist is currently empty.</p>
                    <p><a href="cars.php">Browse our collection</a> to add cars to your wishlist.</p>
                </div>
            <?php else: ?>
                <?php foreach ($wishlistCars as $car): ?>
                    <?php 
                    // Process images - split by comma and trim whitespace
                    $imageFiles = array_map('trim', explode(',', $car['images']));
                    // Filter out empty entries
                    $imageFiles = array_filter($imageFiles);
                    // Get first image or use default
                    $mainImage = !empty($imageFiles) ? 'uploads/car_images/' . $imageFiles[0] : 'img/default-car.jpg';
                    ?>
                    <div class="wheelbay-card">
                        <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>" class="car-image">
                        <h3><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h3>
                        <div class="wheelbay-card-features">
                            <div class="feature">
                                <div class="label">Year</div>
                                <span><?php echo htmlspecialchars($car['year']); ?></span>
                            </div>
                            <div class="feature">
                                <div class="label">Mileage</div>
                                <span><?php echo number_format($car['mileage']); ?> miles</span>
                            </div>
                            <div class="feature">
                                <div class="label">Fuel Type</div>
                                <span><?php echo htmlspecialchars($car['fuel_type']); ?></span>
                            </div>
                        </div>
                        <div class="additional-details">
                            <div class="detail-item">
                                <div class="detail-label">Transmission</div>
                                <div class="detail-value"><?php echo htmlspecialchars($car['transmission']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Color</div>
                                <div class="detail-value"><?php echo htmlspecialchars($car['color']); ?></div>
                            </div>
                        </div>
                        <div class="wheelbay-offer">$<?php echo number_format($car['price'], 2); ?></div>
                        <div class="wheelbay-card-actions">
                            <a href="car_details.php?id=<?php echo $car['id']; ?>" class="wheelbay-banner-button" style="--clr:#17fee3;">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                View Details
                            </a>
                            <a href="order.php?id=<?php echo $car['id']; ?>" class="wheelbay-banner-button" style="--clr:#ff6b6b;">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                Buy Now
                            </a>
                            <button class="wheelbay-wishlist-button" onclick="removeFromWishlist(<?php echo $car['id']; ?>, this)">
                                <ion-icon name="heart"></ion-icon>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="wheelbay-footer">
        <p>&copy; 2023 WheelBay. All rights reserved.</p>
        <div class="wheelbay-social-links">
            <a href="#" aria-label="Facebook" onclick="Social()"><ion-icon name="logo-facebook"></ion-icon></a>
            <a href="#" aria-label="Instagram" onclick="Social()"><ion-icon name="logo-instagram"></ion-icon></a>
            <a href="#" aria-label="Whatsapp" onclick="Social()"><ion-icon name="logo-whatsapp"></ion-icon></a>
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

        function Social() {
            alert("This functionality coming soon!");
        }

        // Remove from wishlist functionality
        function removeFromWishlist(carId, button) {
            const card = button.closest('.wheelbay-card');
            
            fetch('remove_from_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ car_id: carId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove the card from the DOM
                    card.remove();
                    
                    // Check if wishlist is now empty
                    if (document.querySelectorAll('.wheelbay-card').length === 0) {
                        const carList = document.getElementById('car-list');
                        const emptyMessage = document.createElement('div');
                        emptyMessage.className = 'wishlist-empty';
                        emptyMessage.innerHTML = `
                            <p>Your wishlist is currently empty.</p>
                            <p><a href="cars.php">Browse our collection</a> to add cars to your wishlist.</p>
                        `;
                        carList.appendChild(emptyMessage);
                    }
                    
                    alert(data.message || "Car removed from wishlist!");
                } else {
                    alert(data.error || "An error occurred. Please try again.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("A network error occurred. Please check your connection and try again.");
            });
        }
    </script>
</body>
</html>