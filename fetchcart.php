<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

// Ensure $conn is available and is a valid mysqli object
if (!isset($conn) || !($conn instanceof mysqli)) {
     echo json_encode(['status' => 'error', 'message' => 'Database connection not available.']);
     exit;
}

try {
    $stmt = $conn->prepare("SELECT c.id, p.name, p.price, c.quantity, p.image_url
                            FROM cart c
                            JOIN products p ON c.product_id = p.id
                            WHERE c.user_id = ?");
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception('Get result failed: ' . $stmt->error);
    }
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    if ($cart_items === false) {
         throw new Exception('Fetch all failed: ' . $stmt->error);
    }
    $stmt->close();
    
    $total = 0;
    $items = [];
    foreach ($cart_items as $item) {
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
        $items[] = [
            'id' => $item['id'],
            'product_name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image_url' => $item['image_url']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'items' => $items,
        'total' => $total
    ]);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
// The connection is closed in db_connect.php after the script finishes.
?>
