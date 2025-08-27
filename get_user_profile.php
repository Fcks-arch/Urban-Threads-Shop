<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get user profile information
    $stmt = $conn->prepare("SELECT id, full_name, email, phone, address, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Format the created_at date
        if ($user['created_at']) {
            $user['member_since'] = date('M Y', strtotime($user['created_at']));
        }
        
        echo json_encode([
            'status' => 'success',
            'user' => $user
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
    
} catch (Exception $e) {
    error_log('Get user profile error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}

$conn->close();
?>
