<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="css/about.css">
    
</head>
<body class="wheelbay-body">
    <header class="wheelbay-header" id="header">
        <img class="wheelbay-logo" id="logo" src="img/Logo01.png" alt="WheelBay Logo">
        <nav>
            <a href="home.php">Home<span></span></a>
            <a href="cars.php">Cars<span></span></a>
            <a href="wishlist.php">Wishlist<span></span></a>
            <a href="about.php">About<span></span></a>
            <?php if (isset($_SESSION['email']) || isset($_SESSION['admin_email'])): ?>
                <a href="user.php">Profile</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="wheelbay-main">
        <section class="wheelbay-about-section">

            <h1 class="wheelbay-greeting">
            <?php
                if (isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
                    $username = $_SESSION['first_name'] ?? $_SESSION['email'] ?? 'User';
                    echo "Welcome, " . htmlspecialchars($username) . "!";
                } elseif (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
                    echo "Welcome, Guest!";
                }
            ?>
            </h1>

            <h2 class="wheelbay-heading">Our Story</h2>
            <p>
                At WheelBay, we are passionate about connecting car enthusiasts with their dream vehicles. Founded in 2023, our mission is to provide a seamless and enjoyable experience for our customers by offering brand-new cars exclusively from official dealerships. Whether you're looking for a luxury sports car, a reliable family vehicle, or an eco-friendly electric car, WheelBay is your trusted partner.
            </p>
            <p>
                Our platform is designed to make the process of buying cars as easy as possible. With a wide range of vehicles, transparent pricing, and exceptional customer service, we strive to exceed your expectations every step of the way.
            </p>
        </section>

        <section class="wheelbay-about-section">
            <h2 class="wheelbay-heading">Our Mission</h2>
            <p>
                Our mission is to revolutionize the car-buying experience by offering a transparent, reliable, and user-friendly platform that features only brand-new vehicles from official dealerships. We believe everyone deserves access to top-quality cars and outstanding service, and we're committed to making that a reality for every customer.
            </p>
        </section>

        <section class="wheelbay-about-section">
            <h2 class="wheelbay-heading">Why Choose Us</h2>
            <p>
                WheelBay stands out from the competition with our user-friendly interface, comprehensive vehicle history reports, and dedicated customer support available 24/7 to assist you throughout your car-buying journey.
            </p>

            <p>
                Our innovative features include virtual test drives, augmented reality showrooms, and a unique "Try Before You Buy" program for select vehicles. We're constantly evolving to incorporate the latest technologies that enhance your car shopping experience.
                With over 10,000 satisfied customers and partnerships with dealerships across the country, we've established ourselves as a trusted name in the automotive marketplace. Join our community today and experience the difference!
            </p>
        </section>

        <section class="wheelbay-about-section">
            <h2 class="wheelbay-heading">Meet the Team</h2>
            <div class="wheelbay-team">
                <div class="wheelbay-team-member">
                    <img src="img/team1.jpg" alt="Team Member 1">
                    <h3>Krishoth</h3>
                    <p>CEO & Founder</p>
                </div>
                <div class="wheelbay-team-member">
                    <img src="img/suruthi.jpg" alt="Team Member 2">
                    <h3>Suruthigan</h3>
                    <p>Head of Sales</p>
                </div>
                <div class="wheelbay-team-member">
                    <img src="img/Nilavannan.jpg" alt="Team Member 3">
                    <h3>Nilavannan</h3>
                    <p>Lead Developer</p>
                </div>
                <div class="wheelbay-team-member">
                    <img src="img/thulasi.jpg" alt="Team Member 4">
                    <h3>Thulasehaesan</h3>
                    <p>Customer Support</p>
                </div>
            </div>
        </section>
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
        const header = document.getElementById("header");

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