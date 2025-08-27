<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// For single product purchases, we don't require login
$purchase_type = $_POST['purchase_type'] ?? 'cart';

if ($purchase_type === 'cart' && !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

try {
    $user_id = $purchase_type === 'cart' ? $_SESSION['user_id'] : null;
    
    // Get form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    $shipping_method = $_POST['shipping_method'] ?? '';
    $special_instructions = trim($_POST['special_instructions'] ?? '');
    $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
    $voucher_discount = floatval($_POST['voucher_discount'] ?? 0);
    $total = floatval($_POST['total'] ?? 0);
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || 
        empty($address) || empty($city) || empty($postal_code) || empty($payment_method)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }
    
    // Get items based on purchase type
    $items = [];
    if ($purchase_type === 'single') {
        $product_data = json_decode($_POST['product_data'] ?? '{}', true);
        if (empty($product_data) || !isset($product_data['product_name']) || !isset($product_data['price'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product data']);
            exit();
        }
        $items = [$product_data];
    } else {
        $cart_items = json_decode($_POST['cart_items'] ?? '[]', true);
        if (empty($cart_items)) {
            echo json_encode(['status' => 'error', 'message' => 'No items in cart']);
            exit();
        }
        $items = $cart_items;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order record
        if ($user_id) {
            // Logged in user
            $stmt = $conn->prepare("INSERT INTO orders (user_id, first_name, last_name, email, phone, address, city, postal_code, payment_method, shipping_method, special_instructions, shipping_cost, voucher_discount, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("isssssssssdd", $user_id, $first_name, $last_name, $email, $phone, $address, $city, $postal_code, $payment_method, $shipping_method, $special_instructions, $shipping_cost, $voucher_discount, $total);
        } else {
            // Guest user
            $stmt = $conn->prepare("INSERT INTO orders (user_id, first_name, last_name, email, phone, address, city, postal_code, payment_method, shipping_method, special_instructions, shipping_cost, voucher_discount, total_amount, status, created_at) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("ssssssssdd", $first_name, $last_name, $email, $phone, $address, $city, $postal_code, $payment_method, $shipping_method, $special_instructions, $shipping_cost, $voucher_discount, $total);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating order: " . $stmt->error);
        }
        
        $order_id = $conn->insert_id;
        
        // Create order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $stmt->bind_param("issdis", $order_id, $item['id'], $item['product_name'], $item['price'], $item['quantity'], $item['image_url']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating order item: " . $stmt->error);
            }
        }
        
        // If this was a cart purchase, update cart items status
        if ($purchase_type === 'cart' && $user_id) {
            $cart_ids = array_column($items, 'id');
            if (!empty($cart_ids)) {
                $placeholders = str_repeat('?,', count($cart_ids) - 1) . '?';
                $stmt = $conn->prepare("UPDATE cart SET status = 'ordered', order_id = ? WHERE id IN ($placeholders)");
                
                $params = array_merge([$order_id], $cart_ids);
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error updating cart items: " . $stmt->error);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Send confirmation email (you can implement this later)
        // sendOrderConfirmationEmail($email, $order_id, $first_name);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Order placed successfully',
            'order_id' => $order_id
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Checkout error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
