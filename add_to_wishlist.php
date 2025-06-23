<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login to add to wishlist']);
    exit;
}

// Database connection
require_once 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$carId = $data['car_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$carId) {
    echo json_encode(['success' => false, 'error' => 'Invalid car ID']);
    exit;
}

try {
    // Check if already in wishlist
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$userId, $carId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Car already in wishlist']);
        exit;
    }
    
    // Add to wishlist
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, car_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $carId]);
    
    echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>