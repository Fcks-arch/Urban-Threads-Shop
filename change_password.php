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
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit();
    }
    
    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
        exit();
    }
    
    // Validate new password length
    if (strlen($new_password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters long']);
        exit();
    }
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password_hash'])) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        exit();
    }
    
    // Hash the new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password_hash, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    } else {
        throw new Exception("Failed to update password");
    }
    
} catch (Exception $e) {
    error_log('Change password error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}

$conn->close();
?>
