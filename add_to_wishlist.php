<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'You must be logged in to add to wishlist']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['car_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'error' => 'Car ID is required']);
    exit();
}

$carId = $input['car_id'];
$userId = $_SESSION['user_id'];

// Database connection
$host = 'localhost';
$dbname = 'wheelbay';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if car already exists in wishlist
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$userId, $carId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Car is already in your wishlist']);
        exit();
    }
    
    // Add to wishlist
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, car_id) VALUES (?, ?)");
    $stmt->execute([$userId, $carId]);
    
    echo json_encode(['success' => true, 'message' => 'Car added to wishlist']);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>