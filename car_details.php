<?php
// Database connection
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get car ID from URL
    $carId = $_GET['id'] ?? 0;
    
    // Fetch car details
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$carId]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car) {
        header("Location: cars.php");
        exit();
    }
    
    // Process images
    $imageFiles = array_map('trim', explode(',', $car['images']));
    $imageFiles = array_filter($imageFiles);
    $images = [];
    foreach ($imageFiles as $image) {
        $images[] = 'uploads/car_images/' . $image;
    }
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?> - WheelBay</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="css/cars.css">
        <style>
        .car-details-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .car-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .car-title {
            font-size: 2.2rem;
            color: #2c3e50;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .car-price {
            font-size: 2rem;
            color: #17fee3;
            font-weight: bold;
            background: rgba(23, 254, 227, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }
        
        .car-details-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        
        .car-image-gallery {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .main-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .main-image:hover {
            transform: scale(1.02);
        }
        
        .thumbnail-container {
            display: flex;
            gap: 0.8rem;
            padding: 1rem 0;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #17fee3 #f1f1f1;
        }
        
        .thumbnail-container::-webkit-scrollbar {
            height: 6px;
        }
        
        .thumbnail-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .thumbnail-container::-webkit-scrollbar-thumb {
            background-color: #17fee3;
            border-radius: 10px;
        }
        
        .thumbnail {
            width: 100px;
            height: 75px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            flex-shrink: 0;
        }
        
        .thumbnail:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #17fee3;
        }
        
        .thumbnail.active {
            border-color: #17fee3;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(23, 254, 227, 0.3);
        }
        
        .car-specs {
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .car-specs h2 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #17fee3;
            display: inline-block;
        }
        
        .specs-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .spec-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .spec-item:hover {
            background: #f1f1f1;
            transform: translateY(-2px);
        }
        
        .spec-label {
            font-weight: 600;
            color: #7f8c8d;
            display: block;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        
        .spec-value {
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .car-description {
            margin-top: 2rem;
            line-height: 1.8;
            color: #34495e;
        }
        
        .car-description h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #17fee3;
            display: inline-block;
        }
        
        .car-description p {
            margin: 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }
        
        .wheelbay-banner-button {
            flex-grow: 1;
        }
        
        .wheelbay-wishlist-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: #ffffff;
            color: #2c3e50;
            border: 2px solid #17fee3;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-grow: 1;
        }
        
        .wheelbay-wishlist-button:hover {
            background: #17fee3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 254, 227, 0.3);
        }
        
        .wheelbay-wishlist-button ion-icon {
            font-size: 1.2rem;
        }
        
        @media (max-width: 992px) {
            .car-details-content {
                gap: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            .car-details-content {
                grid-template-columns: 1fr;
            }
            
            .car-details-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .main-image {
                height: 350px;
            }
            
            .specs-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .car-title {
                font-size: 1.8rem;
            }
            
            .car-price {
                font-size: 1.6rem;
            }
            
            .main-image {
                height: 280px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .wheelbay-banner-button,
            .wheelbay-wishlist-button {
                width: 100%;
            }
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
        <div class="car-details-container">
            <div class="car-details-header">
                <h1 class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h1>
                <div class="car-price">$<?php echo number_format($car['price'], 2); ?></div>
            </div>
            
            <div class="car-details-content">
                <div class="car-image-gallery">
                    <?php if (!empty($images)): ?>
                        <img id="mainImage" src="<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>" class="main-image">
                        
                        <div class="thumbnail-container">
                            <?php foreach ($images as $index => $image): ?>
                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                     alt="Thumbnail <?php echo $index + 1; ?>" 
                                     class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                     onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <img src="img/default-car.jpg" alt="Default Car Image" class="main-image">
                    <?php endif; ?>
                </div>
                
                <div class="car-specs">
                    <h2>Specifications</h2>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Make</span>
                            <span class="spec-value"><?php echo htmlspecialchars($car['make']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Model</span>
                            <span class="spec-value"><?php echo htmlspecialchars($car['model']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Year</span>
                            <span class="spec-value"><?php echo htmlspecialchars($car['year']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Mileage</span>
                            <span class="spec-value"><?php echo number_format($car['mileage']); ?> miles</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Fuel Type</span>
                            <span class="spec-value"><?php echo htmlspecialchars($car['fuel_type']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Transmission</span>
                            <span class="spec-value"><?php echo htmlspecialchars($car['transmission']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Color</span>
                            <span class="spec-value"><?php echo htmlspecialchars($car['color']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Price</span>
                            <span class="spec-value">$<?php echo number_format($car['price'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="car-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($car['description'] ?: 'No description available.')); ?></p>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="#" class="wheelbay-banner-button" onclick="contactSeller(<?php echo $car['id']; ?>)" style="--clr:#17fee3;">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            Contact Seller
                        </a>
                        <button class="wheelbay-wishlist-button" onclick="addToWishlist(<?php echo $car['id']; ?>, this)">
                            <ion-icon name="heart-outline"></ion-icon> Add to Wishlist
                        </button>
                    </div>
                </div>
            </div>
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

        // Image gallery functionality
        function changeMainImage(src, thumbnail) {
            document.getElementById('mainImage').src = src;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(img => {
                img.classList.remove('active');
            });
            thumbnail.classList.add('active');
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

        function contactSeller(carId) {
            alert(`Contacting seller about car ID: ${carId}`);
            // You can implement a contact form or redirect to a contact page
            // window.location.href = `contact.php?car_id=${carId}`;
        }

        // Auto-scroll thumbnails to show active one
        document.addEventListener('DOMContentLoaded', function() {
            const activeThumbnail = document.querySelector('.thumbnail.active');
            if (activeThumbnail) {
                activeThumbnail.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        });
    </script>
</body>
</html>