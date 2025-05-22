<?php
session_start();
include 'db_connect.php';

// Get order number from POST request
$orderNumber = $_POST['order_number'] ?? '';

if (empty($orderNumber)) {
    echo json_encode(['status' => 'error', 'message' => 'Order number is required']);
    exit;
}

try {
    // Get order details
    $stmt = $conn->prepare("
        SELECT o.*, oi.*, p.image_url 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE o.order_number = ? AND o.user_id = ?
    ");
    $stmt->bind_param("si", $orderNumber, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }

    // Fetch all rows
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get order details from first row
    $order = [
        'order_number' => $rows[0]['order_number'],
        'order_date' => $rows[0]['order_date'],
        'status' => $rows[0]['status'],
        'payment_method' => $rows[0]['payment_method'],
        'total_amount' => $rows[0]['total_amount'],
        'items' => []
    ];

    // Add items to order
    foreach ($rows as $row) {
        if ($row['product_id']) {
            $order['items'][] = [
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'image_url' => $row['image_url']
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'order' => $order
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error tracking order: ' . $e->getMessage()]);
}

$conn->close();
?> 