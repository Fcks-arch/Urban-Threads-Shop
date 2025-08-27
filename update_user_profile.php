<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($full_name) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Full name and email are required']);
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }
    
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email is already taken by another user']);
        exit();
    }
    
    // Update user profile
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        // Update session username if it was the full_name
        if (isset($_SESSION['username']) && $_SESSION['username'] === $_SESSION['full_name']) {
            $_SESSION['username'] = $full_name;
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully'
        ]);
    } else {
        throw new Exception("Failed to update profile");
    }
    
} catch (Exception $e) {
    error_log('Update user profile error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}

$conn->close();
?>
