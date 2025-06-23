<?php
// Database connection
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all cars
    $stmt = $conn->prepare("SELECT * FROM cars");
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $cars = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars</title>
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
    </style>
</head>
<body class="wheelbay-body">
    <header class="wheelbay-header">
        <img class="wheelbay-logo" src="img/Logo01.png" alt="WheelBay Logo">
        <nav>
            <a href="home.html">Home<span></span></a>
            <a href="cars.php">Cars<span></span></a>
            <a href="wishlist.html">Wishlist<span></span></a>
            <a href="about.html">About<span></span></a>
            <a href="login.html">Login<span></span></a>
        </nav>
    </header>

    <main class="wheelbay-main">
        <div class="filter-section">
            <div class="filter-container">
                <label for="brand-filter">Brand:</label>
                <select id="brand-filter" onchange="filterCars()">
                    <option value="all">All Brands</option>
                    <?php
                    // Get unique brands from database
                    $brands = array_unique(array_column($cars, 'make'));
                    foreach ($brands as $brand) {
                        echo "<option value='".htmlspecialchars($brand)."'>".htmlspecialchars($brand)."</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-container">
                <label for="type-filter">Fuel Type:</label>
                <select id="type-filter" onchange="filterCars()">
                    <option value="all">All Types</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Electric">Electric</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
            
            <div class="filter-container">
                <label for="gear-filter">Transmission:</label>
                <select id="gear-filter" onchange="filterCars()">
                    <option value="all">All Types</option>
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                    <option value="Semi-Auto">Semi-Auto</option>
                </select>
            </div>
        </div>

        <div class="search-wrapper">
            <div class="search-container" style="--clr:#00eeff;">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <input type="text" placeholder="Search..." id="search-input" onkeyup="searchCars()">
            </div>
        </div>

        <h2 class="wheelbay-heading">Explore Our Luxury Car Collection</h2>
        <div class="wheelbay-car-list" id="car-list">
            <?php if (isset($error)): ?>
                <div class="error-message">Database Error: <?php echo htmlspecialchars($error); ?></div>
            <?php elseif (empty($cars)): ?>
                <div class="no-cars-message">No cars available at the moment.</div>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <?php 
                    // Process images - split by comma and trim whitespace
                    $imageFiles = array_map('trim', explode(',', $car['images']));
                    // Filter out empty entries
                    $imageFiles = array_filter($imageFiles);
                    // Get first image or use default
                    $mainImage = !empty($imageFiles) ? 'uploads/car_images/' . $imageFiles[0] : 'img/default-car.jpg';
                    ?>
                    <div class="wheelbay-card" 
                         data-brand="<?php echo htmlspecialchars($car['make']); ?>" 
                         data-type="<?php echo htmlspecialchars($car['fuel_type']); ?>" 
                         data-gear="<?php echo htmlspecialchars($car['transmission']); ?>" 
                         data-color="<?php echo htmlspecialchars($car['color']); ?>"
                         data-name="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
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
                            <button class="wheelbay-wishlist-button" onclick="addToWishlist(<?php echo $car['id']; ?>, this)">
                                <ion-icon name="heart-outline"></ion-icon>
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

        // Wishlist functionality
        function addToWishlist(carId, button) {
            const heartIcon = button.querySelector('ion-icon');
            
            // Toggle heart icon
            if (heartIcon.getAttribute('name') === 'heart-outline') {
                heartIcon.setAttribute('name', 'heart');
                
                // Send AJAX request to add to wishlist
                fetch('add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ car_id: carId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || "Car added to wishlist!");
                    } else {
                        alert(data.error || "Failed to add to wishlist");
                        heartIcon.setAttribute('name', 'heart-outline');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    heartIcon.setAttribute('name', 'heart-outline');
                    alert("An error occurred. Please try again.");
                });
                
            } else {
                heartIcon.setAttribute('name', 'heart-outline');
                
                // Send AJAX request to remove from wishlist
                fetch('remove_from_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ car_id: carId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || "Car removed from wishlist!");
                    } else {
                        alert(data.error || "Failed to remove from wishlist");
                        heartIcon.setAttribute('name', 'heart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    heartIcon.setAttribute('name', 'heart');
                    alert("An error occurred. Please try again.");
                });
            }
        }

        // Filter functionality
        function filterCars() {
            const brandFilter = document.getElementById('brand-filter').value.toLowerCase();
            const typeFilter = document.getElementById('type-filter').value.toLowerCase();
            const gearFilter = document.getElementById('gear-filter').value.toLowerCase();
            const cars = document.querySelectorAll('.wheelbay-card');
            let visibleCount = 0;
            
            // Remove any existing "no cars" message
            const existingMessage = document.querySelector('.no-cars-message');
            if (existingMessage && existingMessage.classList.contains('filter-message')) {
                existingMessage.remove();
            }
            
            cars.forEach(car => {
                const carBrand = car.dataset.brand.toLowerCase();
                const carType = car.dataset.type.toLowerCase();
                const carGear = car.dataset.gear.toLowerCase();
                
                const brandMatch = brandFilter === 'all' || carBrand === brandFilter;
                const typeMatch = typeFilter === 'all' || carType === typeFilter;
                const gearMatch = gearFilter === 'all' || carGear === gearFilter;
                
                if (brandMatch && typeMatch && gearMatch) {
                    car.style.display = 'block';
                    visibleCount++;
                } else {
                    car.style.display = 'none';
                }
            });
            
            // Show message if no cars match the filters
            if (visibleCount === 0) {
                const carList = document.getElementById('car-list');
                const noCarsMessage = document.createElement('div');
                noCarsMessage.className = 'no-cars-message filter-message';
                noCarsMessage.textContent = 'No cars match your selected filters.';
                carList.appendChild(noCarsMessage);
            }
        }

        // Search functionality
        function searchCars() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const cars = document.querySelectorAll('.wheelbay-card');
            let visibleCount = 0;
            
            // Remove any existing "no cars" message
            const existingMessage = document.querySelector('.no-cars-message');
            if (existingMessage && existingMessage.classList.contains('search-message')) {
                existingMessage.remove();
            }
            
            cars.forEach(car => {
                const carName = car.dataset.name.toLowerCase();
                
                if (carName.includes(searchTerm)) {
                    car.style.display = 'block';
                    visibleCount++;
                } else {
                    car.style.display = 'none';
                }
            });
            
            // Show message if no cars match the search
            if (visibleCount === 0 && searchTerm) {
                const carList = document.getElementById('car-list');
                const noCarsMessage = document.createElement('div');
                noCarsMessage.className = 'no-cars-message search-message';
                noCarsMessage.textContent = 'No cars match your search.';
                carList.appendChild(noCarsMessage);
            } else if (!searchTerm) {
                // If search is empty, show all cars
                cars.forEach(car => {
                    car.style.display = 'block';
                });
                filterCars(); // Reapply any active filters
            }
        }
    </script>
</body>
</html>