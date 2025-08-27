<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

// Get order number from POST request
$orderNumber = $_POST['order_number'] ?? '';

if (empty($orderNumber)) {
    // If no order number is provided, fetch pending checkout requests from the cart
    if (!isset($_SESSION['user_id'])) {
         echo json_encode(['status' => 'error', 'message' => 'Please login to view pending requests.']);
         exit;
    }

    $user_id = $_SESSION['user_id'];
    
    // Fetch cart items with status 'pending_checkout' or 'approved' for the logged-in user
    // We also need to fetch the tracking number for approved items.
    $sql_pending = "SELECT c.id as cart_id, c.product_name, c.quantity, c.price, c.status, c.tracking_number, p.image_url FROM cart c JOIN users u ON c.user_id = u.id JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND (c.status = 'pending_checkout' OR c.status = 'approved')";
    
    if ($stmt_pending = $conn->prepare($sql_pending)) {
        $stmt_pending->bind_param("i", $user_id);
        $stmt_pending->execute();
        $result_pending = $stmt_pending->get_result();
        $pending_items = $result_pending->fetch_all(MYSQLI_ASSOC);
        $stmt_pending->close();

        if (empty($pending_items)) {
             echo json_encode(['status' => 'error', 'message' => 'No pending or approved checkout requests found.']);
             exit;
        }

        echo json_encode([
            'status' => 'success',
            'pending_requests' => $pending_items // Return pending items (which now include approved items and tracking numbers)
        ]);

    } else {
         error_log('Track Order Pending Fetch Prepare Error: ' . $conn->error);
         echo json_encode(['status' => 'error', 'message' => 'Error fetching pending requests.']);
    }

} else {
    // If an order number is provided, fetch order details from the orders table
    try {
        // Get order details
        $stmt = $conn->prepare("
            SELECT o.*, oi.*, p.image_url, o.tracking_number 
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
            'tracking_number' => $rows[0]['tracking_number'] ?? null, // Assuming a tracking_number column
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
        error_log('Error tracking order: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error tracking order: ' . $e->getMessage()]);
    }
}

$conn->close();
?> 