<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database connection
require_once 'db_connection.php';

// Fetch user's wishlist
try {
    $stmt = $conn->prepare("
        SELECT c.* 
        FROM cars c
        JOIN wishlist w ON c.id = w.car_id
        WHERE w.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Failed to load wishlist: " . $e->getMessage();
    $wishlistItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist | WheelBay</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        /* Add your existing styles here */
        .empty-wishlist {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
        }
        
        .empty-wishlist h3 {
            color: #17fee3;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        
        .empty-wishlist a {
            color: #17fee3;
            text-decoration: none;
            border: 2px solid #17fee3;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .empty-wishlist a:hover {
            background-color: #17fee3;
            color: #000;
        }
        
        .wishlist-count {
            background-color: #17fee3;
            color: #000;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8em;
            margin-left: 5px;
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
        <h2 class="wheelbay-heading">Your Wishlist <span class="wishlist-count" id="wishlist-count"><?php echo count($wishlistItems); ?></span></h2>
        
        <div class="wheelbay-car-list" id="wishlist-container">
            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif(empty($wishlistItems)): ?>
                <div class="empty-wishlist">
                    <h3>Your Wishlist is Empty</h3>
                    <p>You haven't added any cars to your wishlist yet.</p>
                    <a href="cars.php">Browse Cars</a>
                </div>
            <?php else: ?>
                <?php foreach($wishlistItems as $car): ?>
                    <?php 
                    $images = array_map('trim', explode(',', $car['images']));
                    $images = array_filter($images);
                    $mainImage = !empty($images) ? 'uploads/car_images/' . $images[0] : 'img/default-car.jpg';
                    ?>
                    <div class="wheelbay-card" data-car-id="<?php echo $car['id']; ?>">
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
        <p>&copy; <?php echo date('Y'); ?> WheelBay. All rights reserved.</p>
        <div class="wheelbay-social-links">
            <a href="#" aria-label="Facebook" onclick="Social()"><ion-icon name="logo-facebook"></ion-icon></a>
            <a href="#" aria-label="Instagram" onclick="Social()"><ion-icon name="logo-instagram"></ion-icon></a>
            <a href="#" aria-label="Whatsapp" onclick="Social()"><ion-icon name="logo-whatsapp"></ion-icon></a>
        </div>
    </footer>

    <script>
        // Function to remove item from wishlist
        async function removeFromWishlist(carId, button) {
            try {
                const response = await fetch('remove_from_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ car_id: carId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove the card from UI with animation
                    const card = button.closest('.wheelbay-card');
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        updateWishlistCount();
                        
                        // If no more items, show empty message
                        if (document.querySelectorAll('.wheelbay-card').length === 0) {
                            showEmptyWishlist();
                        }
                    }, 300);
                    
                    alert(data.message || "Car removed from wishlist!");
                } else {
                    alert(data.error || "Failed to remove from wishlist");
                }
            } catch (error) {
                console.error('Error:', error);
                alert("An error occurred. Please try again.");
            }
        }
        
        // Update wishlist count
        function updateWishlistCount() {
            const count = document.querySelectorAll('.wheelbay-card').length;
            const countElement = document.getElementById('wishlist-count');
            if (countElement) {
                countElement.textContent = count;
            }
        }
        
        // Show empty wishlist message
        function showEmptyWishlist() {
            const container = document.getElementById('wishlist-container');
            container.innerHTML = `
                <div class="empty-wishlist">
                    <h3>Your Wishlist is Empty</h3>
                    <p>You haven't added any cars to your wishlist yet.</p>
                    <a href="cars.php">Browse Cars</a>
                </div>
            `;
        }
        
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
    </script>
</body>
</html>