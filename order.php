<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=order&id=".($_GET['id'] ?? ''));
    exit();
}

// Check if car ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$carId = (int)$_GET['id'];

// Database connection
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get car details
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$carId]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car) {
        header("Location: cars.php");
        exit();
    }
    
    // Get user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insert order into database
        $stmt = $conn->prepare("
            INSERT INTO orders 
            (user_id, car_id, fullname, email, phone, address, payment_method, amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $carId,
            $_POST['fullname'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address'],
            $_POST['payment'],
            $car['price']
        ]);
        
        // Redirect to success page
        header("Location: order_success.php?id=".$conn->lastInsertId());
        exit();
        
    } catch(PDOException $e) {
        $error = "Error processing order: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order <?php echo htmlspecialchars($car['make'].' '.$car['model']); ?> | WheelBay</title>
    <link rel="stylesheet" href="css/cars.css">
    <style>
        .order-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #1a1a2e;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 238, 255, 0.1);
        }
        .order-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .car-image {
            width: 100%;
            border-radius: 8px;
            height: auto;
            max-height: 300px;
            object-fit: cover;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #00eeff;
        }
        input, select {
            width: 100%;
            padding: 0.75rem;
            background: #16213e;
            border: 1px solid #00eeff;
            border-radius: 5px;
            color: white;
        }
        .total-price {
            font-size: 1.5rem;
            text-align: right;
            margin: 2rem 0;
            color: #00eeff;
        }
        .error-message {
            color: #ff5555;
            margin-bottom: 1rem;
            text-align: center;
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
        <div class="order-container">
            <div class="order-header">
                <h2>Complete Your Purchase</h2>
                <p>Order for <?php echo htmlspecialchars($car['make'].' '.$car['model']); ?></p>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="order-details">
                <div>
                    <?php 
                    $imageFiles = array_map('trim', explode(',', $car['images']));
                    $imageFiles = array_filter($imageFiles);
                    $mainImage = !empty($imageFiles) ? 'uploads/car_images/' . $imageFiles[0] : 'img/default-car.jpg';
                    ?>
                    <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($car['make'].' '.$car['model']); ?>" class="car-image">
                    <div class="car-specs">
                        <h3><?php echo htmlspecialchars($car['make'].' '.$car['model']); ?></h3>
                        <p>Year: <?php echo htmlspecialchars($car['year']); ?></p>
                        <p>Mileage: <?php echo number_format($car['mileage']); ?> miles</p>
                        <p>Color: <?php echo htmlspecialchars($car['color']); ?></p>
                    </div>
                </div>
                
                <div>
                    <form method="POST">
                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" 
                                   value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Delivery Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment">Payment Method</label>
                            <select id="payment" name="payment" required>
                                <option value="">Select payment method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                        
                        <div class="total-price">
                            Total: $<?php echo number_format($car['price'], 2); ?>
                        </div>
                        
                        <button type="submit" class="wheelbay-banner-button" style="--clr:#ff6b6b; width: 100%;">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            Confirm Order
                        </button>
                    </form>
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
    </script>
</body>
</html>