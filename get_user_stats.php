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
    
    // Get user statistics
    $stats = [
        'total_orders' => 0,
        'pending_orders' => 0,
        'completed_orders' => 0,
        'member_since' => '-'
    ];
    
    // Get member since date
    $stmt = $conn->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['created_at']) {
            $stats['member_since'] = date('M Y', strtotime($user['created_at']));
        }
    }
    
    // Get cart/order statistics (assuming cart items represent orders)
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM cart WHERE user_id = ? GROUP BY status");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $stats['total_orders'] += $row['count'];
        
        if ($row['status'] === 'pending_checkout' || $row['status'] === 'pending') {
            $stats['pending_orders'] += $row['count'];
        } elseif ($row['status'] === 'approved' || $row['status'] === 'completed') {
            $stats['completed_orders'] += $row['count'];
        }
    }
    
    // If no specific status orders, count all cart items as total
    if ($stats['total_orders'] === 0) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['total_orders'] = $row['count'];
    }
    
    echo json_encode([
        'status' => 'success',
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log('Get user stats error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}

$conn->close();
?>
