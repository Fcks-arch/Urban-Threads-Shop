<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

// Get form data
$fullName = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';
$totalAmount = $_POST['total_amount'] ?? 0;

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone) || empty($address) || empty($paymentMethod)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Generate order number
    $orderNumber = 'ORD' . date('YmdHis') . rand(1000, 9999);

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, full_name, email, phone, address, payment_method, total_amount, status, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Order Placed', NOW())");
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("sisssssd", $orderNumber, $_SESSION['user_id'], $fullName, $email, $phone, $address, $paymentMethod, $totalAmount);
    if ($stmt->execute() === false) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    $orderId = $conn->insert_id;

    // Get cart items
    $stmt = $conn->prepare("SELECT c.*, p.image_url FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    if ($stmt === false) {
        throw new Exception('Prepare failed for cart items: ' . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    if ($stmt->execute() === false) {
        throw new Exception('Execute failed for cart items: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception('Get result failed: ' . $stmt->error);
    }
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    if ($cartItems === false) {
        throw new Exception('Fetch all failed: ' . $stmt->error);
    }

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        throw new Exception('Prepare failed for order items: ' . $conn->error);
    }
    foreach ($cartItems as $item) {
        $stmt->bind_param("iisid", $orderId, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']);
        if ($stmt->execute() === false) {
            throw new Exception('Execute failed for order item ' . $item['product_name'] . ': ' . $stmt->error);
        }
    }

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    if ($stmt === false) {
        throw new Exception('Prepare failed for clear cart: ' . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    if ($stmt->execute() === false) {
        throw new Exception('Execute failed for clear cart: ' . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Order placed successfully',
        'order_number' => $orderNumber
    ]);

} catch (Exception $e) {
    // Log the error to a file
    file_put_contents('placeorder_error_log.txt', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);

    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Error placing order: ' . $e->getMessage()]);
}

$conn->close();
?> 