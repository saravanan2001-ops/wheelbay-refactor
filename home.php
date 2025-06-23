<?php
session_start();
$loggedInUserId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WheelBay</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="css/home.css">
</head>
<style>
    
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #121212;
            z-index: 9999;
        }

        #loading video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .loading-text {
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            animation: fadeIn 1.5s ease-in-out infinite;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            50% { opacity: 1; }
            100% { opacity: 0; }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }


        .wheelbay-body {
            background-color: black;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            line-height: 1.6;
            display: none;
        }


        .wheelbay-link {
            text-decoration: none;
            color: inherit;
        }

        /* Specific CSS for the header */
        .wheelbay-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 25px;
            height: 80px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        /* Specific CSS for the logo */
        .wheelbay-logo {
            width: 120px;
            transition: transform 0.3s;
        }

        .wheelbay-logo:hover {
            transform: scale(1.1);
        }

        /* Specific CSS for navigation links */
        nav a {
            position: relative;
            font-size: 1.1em;
            color: #fff;
            text-decoration: none;
            padding: 6px 20px;
            transition: .5s;
        }

        nav a:hover {
            color: #0ef;
        }

        nav a span {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            border-bottom: 2px solid #0ef;
            border-radius: 15px;
            transform: scale(0) translateY(50px);
            opacity: 0;
            transition: .5s;
        }

        nav a:hover span {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* Specific CSS for the banner */
        .wheelbay-banner {
            width: 100%;
            height: 100vh;
            background-image: url('img/Banner.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            padding-left: 20px;
            position: relative;
            padding-top: 80px;
        }

        /* Specific CSS for the banner column */
        .wheelbay-banner-col {
            display: flex;
            flex-direction: column;
            align-items: 5%;
            gap: 10px;
            padding-left: 5%;
        }

        .wheelbay-banner-col h1 {
            color: #ffff;
            font-size: 100px;
            margin: 0;
            padding: 0;
            line-height: 1;
        }

        .wheelbay-banner-col h1.wheelbay-blue {
            color: #17fee3;
            margin-top: -15px;
        }

        /* Specific CSS for banner buttons */
        .wheelbay-banner-button {
            background: linear-gradient(135deg, #17fee3, #14d3c3);
            border: none;
            color: #121212;
            padding: 15px 30px;
            font-size: 1.1em;
            border-radius: 25px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            font-weight: bold;
        }

        .wheelbay-banner-button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(23, 254, 227, 0.5);
        }

        /* Specific CSS for the main content */
        .wheelbay-main {
            padding: 40px 25px;
        }

        /* Specific CSS for headings */
        .wheelbay-heading {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
            color: #17fee3;
        }

        /* Specific CSS for the carousel container */
        .wheelbay-carousel-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 40px 0;
            overflow: hidden;
            width: 100%;
        }

        /* Specific CSS for the carousel */
        .wheelbay-carousel {
            display: flex;
            width: max-content;
            animation: scroll 30s linear infinite;
            gap: 20px;
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-75%);
            }
        }

        /* Specific CSS for cards */
        .wheelbay-card {
            min-width: 280px;
            margin: 0 15px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            background: #1f1f1f;
            text-align: center;
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .wheelbay-carousel-container .wheelbay-card{
            background-color: transparent;
        }

        .wheelbay-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }

        .wheelbay-card img {
            width: 80px;
            height: 80px;
            display: block;
            margin: 0 auto 15px;
            border-radius: 50%;
            object-fit: cover;
        }

        .wheelbay-card h3 {
            margin: 15px 0;
            font-size: 1.5em;
        }

        .wheelbay-card p {
            margin: 10px 0;
            font-size: 0.95em;
            color: #ccc;
        }

        /* Specific CSS for ratings */
        .wheelbay-rating {
            color: rgba(23, 254, 227, 0.5);
            font-size: 1.2em;
            margin: 10px 0;
        }

        /* Specific CSS for the review list */
        .wheelbay-review-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            padding: 25px;
        }

        /* Specific CSS for the footer */
        .wheelbay-footer {
            background-color: #1f1f1f;
            color: white;
            text-align: center;
            padding: 40px 20px;
            margin-top: 40px;
        }

        .wheelbay-footer p {
            margin: 0;
        }

        /* Specific CSS for social links */
        .wheelbay-social-links {
            margin-top: 20px;
        }

        .wheelbay-social-links a {
            margin: 0 10px;
            font-size: 1.5em;
            color: #17fee3;
            transition: color 0.3s;
        }

        .wheelbay-social-links a:hover {
            color: #14d3c3;
        }

        .wheelbay-social-links img {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }

        .wheelbay-social-links a:hover img {
            transform: scale(1.2);
        }

        /* Home Container Styles */
        .wheelbay-home-container {
            padding: 60px 25px;
            background-color: #121212;
        }

        .wheelbay-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .wheelbay-feature-card {
            background: #1f1f1f;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #2a2a2a;
        }

        .wheelbay-feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(23, 254, 227, 0.1);
            border-color: #17fee3;
        }

        .wheelbay-feature-icon {
            font-size: 2.5rem;
            color: #17fee3;
            margin-bottom: 20px;
        }

        .wheelbay-feature-card h3 {
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .wheelbay-feature-card p {
            color: #ccc;
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Stats Section */
        .wheelbay-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 60px 0;
            text-align: center;
        }

        .wheelbay-stat-item {
            padding: 25px;
            background: rgba(23, 254, 227, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(23, 254, 227, 0.2);
        }

        .wheelbay-stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #17fee3;
            margin-bottom: 10px;
        }

        .wheelbay-stat-label {
            color: #fff;
            font-size: 1.1rem;
        }

        /* How It Works Section */
        .wheelbay-steps {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
        }

        .wheelbay-step {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            position: relative;
            padding: 30px;
            background: #1f1f1f;
            border-radius: 12px;
            text-align: center;
        }

        .wheelbay-step-number {
            width: 40px;
            height: 40px;
            background: #17fee3;
            color: #121212;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 20px;
        }

        /* Call to Action */
        .wheelbay-cta {
            text-align: center;
            padding: 60px 25px;
            background: linear-gradient(135deg, rgba(23, 254, 227, 0.1), rgba(20, 211, 195, 0.1));
            margin: 60px 0;
            border-radius: 15px;
        }

        .wheelbay-cta h2 {
            color: #17fee3;
            font-size: 2.2rem;
            margin-bottom: 20px;
        }

        .wheelbay-cta p {
            color: #ccc;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 30px;
        }

        /* Media queries */
        @media (max-width: 768px) {
            .wheelbay-carousel {
                width: 100%;
            }

            .wheelbay-card {
                min-width: 220px;
            }
            
            .wheelbay-steps {
                flex-direction: column;
                align-items: center;
            }
            
            .wheelbay-step {
                max-width: 100%;
            }
            
            .wheelbay-banner-col h1 {
                font-size: 70px;
            }
            
            .wheelbay-banner-col h1.wheelbay-blue {
                font-size: 70px;
            }
        }
        
        @media (max-width: 480px) {
            .wheelbay-banner-col h1,
            .wheelbay-banner-col h1.wheelbay-blue {
                font-size: 50px;
            }
            
            nav a {
                padding: 6px 10px;
                font-size: 0.9em;
            }
            
            .wheelbay-logo {
                width: 90px;
            }
        }

</style>
<body>
    <!-- Loading Screen -->
    <div id="loading">
        <video autoplay muted loop>
            <source src="img/loading.mp4" type="video/mp4">
        </video>
        <div class="loading-text">Loading WheelBay Home...</div>
    </div>

    <!-- Main Website Content (initially hidden) -->
    <div class="wheelbay-body">
        <header class="wheelbay-header" id="header">
            <img class="wheelbay-logo" id="logo" src="img/Logo01.png" alt="WheelBay Logo">
            <nav>
            <a href="home.php">Home<span></span></a>
            <a href="cars.php">Cars<span></span></a>
            <a href="wishlist.php">Wishlist<span></span></a>
            <a href="about.php">About<span></span></a>
            <?php if(isset($_SESSION['username'])): ?>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                    <?php endif; ?>
            </nav>
        </header>

        <div class="wheelbay-banner">
            <div class="wheelbay-banner-col">
                <h1>Welcome to </h1>
                <h1 class="wheelbay-blue">WheelBay</h1>
                <div>
                    <button class="wheelbay-banner-button" onclick="buyCar()">BUY</button>
                    <button class="wheelbay-banner-button" onclick="sellCar()">SELL</button>
                </div>
            </div>
        </div>

        <main class="wheelbay-main">
            <section class="wheelbay-home-container">
                <h2 class="wheelbay-heading">Why Choose WheelBay?</h2>
                <div class="wheelbay-features">
                    <div class="wheelbay-feature-card">
                        <div class="wheelbay-feature-icon">
                            <ion-icon name="car-sport-outline"></ion-icon>
                        </div>
                        <h3>Premium Selection</h3>
                        <p>Access to the finest collection of luxury and performance vehicles from around the world, all in one place.</p>
                    </div>
                    <div class="wheelbay-feature-card">
                        <div class="wheelbay-feature-icon">
                            <ion-icon name="shield-checkmark-outline"></ion-icon>
                        </div>
                        <h3>Verified Listings</h3>
                        <p>Every vehicle undergoes rigorous inspection to ensure quality and authenticity before listing.</p>
                    </div>
                    <div class="wheelbay-feature-card">
                        <div class="wheelbay-feature-icon">
                            <ion-icon name="cash-outline"></ion-icon>
                        </div>
                        <h3>Competitive Pricing</h3>
                        <p>Get the best value with our price match guarantee and transparent pricing policy.</p>
                    </div>
                </div>

                <div class="wheelbay-stats">
                    <div class="wheelbay-stat-item">
                        <div class="wheelbay-stat-number">500+</div>
                        <div class="wheelbay-stat-label">Premium Vehicles</div>
                    </div>
                    <div class="wheelbay-stat-item">
                        <div class="wheelbay-stat-number">98%</div>
                        <div class="wheelbay-stat-label">Customer Satisfaction</div>
                    </div>
                    <div class="wheelbay-stat-item">
                        <div class="wheelbay-stat-number">24/7</div>
                        <div class="wheelbay-stat-label">Support Available</div>
                    </div>
                    <div class="wheelbay-stat-item">
                        <div class="wheelbay-stat-number">50+</div>
                        <div class="wheelbay-stat-label">Brands Available</div>
                    </div>
                </div>

                <h2 class="wheelbay-heading">How It Works</h2>
                <div class="wheelbay-steps">
                    <div class="wheelbay-step">
                        <div class="wheelbay-step-number">1</div>
                        <h3>Browse or List</h3>
                        <p>Explore our extensive inventory or list your vehicle for sale with just a few clicks.</p>
                    </div>
                    <div class="wheelbay-step">
                        <div class="wheelbay-step-number">2</div>
                        <h3>Connect</h3>
                        <p>Get in touch with sellers or buyers through our secure messaging system.</p>
                    </div>
                    <div class="wheelbay-step">
                        <div class="wheelbay-step-number">3</div>
                        <h3>Complete Transaction</h3>
                        <p>Finalize your purchase or sale with our assistance and documentation support.</p>
                    </div>
                </div>

                <div class="wheelbay-cta">
                    <h2>Ready to Find Your Dream Car?</h2>
                    <p>Join thousands of satisfied customers who found their perfect vehicle through WheelBay.</p>
                    <button class="wheelbay-banner-button" onclick="buyCar()">Browse Inventory</button>
                </div>
            </section>

            <section class="container">
                <h2 class="wheelbay-heading">Featured Car Models</h2>
                <div class="wheelbay-carousel-container">
                    <div class="wheelbay-carousel" id="carousel">
                        <div class="wheelbay-card">
                            <img src="img/ferrari01.png" alt="Ferrari">
                            <h3>Ferrari</h3>
                            <p>Luxury sports car with high performance.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/lambo01.png" alt="Lamborghini">
                            <h3>Lamborghini</h3>
                            <p>Iconic supercar with cutting-edge design.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/tesla01.png" alt="Tesla">
                            <h3>Tesla</h3>
                            <p>Innovative electric vehicle technology.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/bmw01.png" alt="BMW">
                            <h3>BMW</h3>
                            <p>Precision engineering and luxury combined.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/mercedes.png" alt="Mercedes-Benz">
                            <h3>Mercedes-Benz</h3>
                            <p>Elegance and advanced technology.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/audi01.png" alt="Audi">
                            <h3>Audi</h3>
                            <p>Progressive design and performance.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/porsche01.png" alt="Porsche">
                            <h3>Porsche</h3>
                            <p>Luxury performance sports cars.</p>
                        </div>
                        
                        <div class="wheelbay-card">
                            <img src="img/ferrari01.png" alt="Ferrari">
                            <h3>Ferrari</h3>
                            <p>Luxury sports car with high performance.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/lambo01.png" alt="Lamborghini">
                            <h3>Lamborghini</h3>
                            <p>Iconic supercar with cutting-edge design.</p>
                        </div>
                        <div class="wheelbay-card">
                            <img src="img/tesla01.png" alt="Tesla">
                            <h3>Tesla</h3>
                            <p>Innovative electric vehicle technology.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="container">
                <h2 class="wheelbay-heading">Customer Reviews</h2>
                <div class="wheelbay-review-list">
                    <!-- 5-star rating -->
                    <div class="wheelbay-card">
                        <img src="img/team3.jpg" alt="User 1">
                        <h3>Charlize Theron</h3>
                        <div class="wheelbay-rating">
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                        </div>
                        <p>"WheelBay made buying my dream car so easy! The process was smooth, and the team was very helpful."</p>
                    </div>

                    <!-- 4.5-star rating -->
                    <div class="wheelbay-card">
                        <img src="img/g2.jpg" alt="User 2">
                        <h3>Emilia clarke</h3>
                        <div class="wheelbay-rating">
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star-half"></ion-icon>
                        </div>
                        <p>"Great experience selling my car through WheelBay. Highly recommend their services!"</p>
                    </div>

                    <!-- 5-star rating -->
                    <div class="wheelbay-card">
                        <img src="img/b2.jpg" alt="User 3">
                        <h3>Michael Brown</h3>
                        <div class="wheelbay-rating">
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                        </div>
                        <p>"Excellent platform for car enthusiasts. Found the perfect car at a great price."</p>
                    </div>

                    <!-- 4.5-star rating -->
                    <div class="wheelbay-card">
                        <img src="img/g1.jpg" alt="User 4">
                        <h3>Emily Davis</h3>
                        <div class="wheelbay-rating">
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star-half"></ion-icon>
                            <ion-icon name="star-outline"></ion-icon>
                        </div>
                        <p>"WheelBay has a wide range of cars to choose from. The customer support is top-notch."</p>
                    </div>

                    <!-- 5-star rating -->
                    <div class="wheelbay-card">
                        <img src="img/b1.jpg" alt="User 5">
                        <h3>David Wilson</h3>
                        <div class="wheelbay-rating">
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                        </div>
                        <p>"I had a fantastic experience buying my first car through WheelBay. Highly satisfied!"</p>
                    </div>

                    <!-- 4.5-star rating -->
                    <div class="wheelbay-card">
                        <img src="img/g3.jpg" alt="User 6">
                        <h3>Sarah Johnson</h3>
                        <div class="wheelbay-rating">
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star"></ion-icon>
                            <ion-icon name="star-half"></ion-icon>
                        </div>
                        <p>"WheelBay is my go-to platform for buying and selling cars. Highly reliable!"</p>
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
    </div>

    <script>
        // Loading screen functionality
        window.onload = () => {
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
                document.querySelector('.wheelbay-body').style.display = 'block';
            }, 5000); // 5 seconds delay for loading screen
        };

        // Original website functionality
        const header = document.getElementById("header");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                header.style.backgroundColor = "#121212";
                header.style.boxShadow = "0 2px 10px rgba(0, 0, 0, 0.3)";
            } else {
                header.style.backgroundColor = "transparent";
                header.style.boxShadow = "none";
            }
        });

        function buyCar() {
            window.location.href = "cars.html";
        }

        function sellCar() {
            window.location.href = "login.html";
        }

        function Social() {
            alert("This functionality coming soon!");
        }
    </script>
</body>

</html>
