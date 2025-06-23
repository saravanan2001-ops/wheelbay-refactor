<?php
$servername = "localhost"; // Replace with your database server name
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "wheelbay"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Log the error instead of displaying it publicly in a production environment
    error_log("Database Connection failed: " . $conn->connect_error);
    die("Sorry, there was a problem connecting to the database." . $conn->connect_error); // Generic error for user
}

// Optional: Set the charset to UTF-8 for proper character handling
$conn->set_charset("utf8mb4");

// Note: In a real application, you might use PDO for database interactions
// and implement more robust error handling.
?>