<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$orderId = (int)$_GET['id'];

// Database connection
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get order details
    $stmt = $conn->prepare("
        SELECT o.*, c.make, c.model, c.year 
        FROM orders o
        JOIN cars c ON o.car_id = c.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header("Location: cars.php");
        exit();
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
    <title>Order Confirmed | WheelBay</title>
    <link rel="stylesheet" href="css/cars.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #1a1a2e;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 238, 255, 0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: #4CAF50;
            margin-bottom: 1rem;
        }
        .order-details {
            margin: 2rem 0;
            text-align: left;
            padding: 1rem;
            background: #16213e;
            border-radius: 8px;
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
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h2>Order Confirmed!</h2>
            <p>Thank you for your purchase. Your order has been received and is being processed.</p>
            
            <div class="order-details">
                <h3>Order #<?php echo $order['id']; ?></h3>
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($order['make'].' '.$order['model'].' ('.$order['year'].')'); ?></p>
                <p><strong>Amount Paid:</strong> $<?php echo number_format($order['amount'], 2); ?></p>
                <p><strong>Delivery to:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
            </div>
            
            <p>We'll contact you shortly with delivery details. You'll receive a confirmation email at <?php echo htmlspecialchars($order['email']); ?></p>
            
            <a href="cars.php" class="wheelbay-banner-button" style="--clr:#17fee3; display: inline-block; margin-top: 2rem;">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                Continue Shopping
            </a>
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