<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate input data
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = 1; // Default quantity

    // Validate inputs
    if (!$product_id || empty($product_name) || !$price) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product data']);
        exit;
    }

    // Ensure $conn is available and is a valid mysqli object
    if (!isset($conn) || !($conn instanceof mysqli)) {
         echo json_encode(['status' => 'error', 'message' => 'Database connection not available.']);
         exit;
    }

    try {
        // Check if product exists and has stock
        $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        if ($stmt === false) {
            throw new Exception('Prepare failed (stock check): ' . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception('Product not found');
        }

        if ($product['stock_quantity'] < 1) {
            throw new Exception('Product out of stock');
        }

        // Start transaction
        $conn->begin_transaction();

        // Check if product already exists in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
         if ($stmt === false) {
            throw new Exception('Prepare failed (cart check): ' . $conn->error);
        }
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_item = $result->fetch_assoc();
        $stmt->close();

        if ($existing_item) {
            // Update quantity if product exists
            $new_quantity = $existing_item['quantity'] + 1;

            // Check if new quantity exceeds stock
            if ($new_quantity > $product['stock_quantity']) {
                throw new Exception('Not enough stock available');
            }

            $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
             if ($stmt === false) {
                throw new Exception('Prepare failed (cart update): ' . $conn->error);
            }
            $stmt->bind_param("ii", $new_quantity, $existing_item['id']);
            $stmt->execute();
             $stmt->close();
        } else {
            // Insert new product
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, price, quantity, created_at, updated_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
             if ($stmt === false) {
                throw new Exception('Prepare failed (cart insert): ' . $conn->error);
            }
            $stmt->bind_param("iisid", $user_id, $product_id, $product_name, $price, $quantity);
            $stmt->execute();
             $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
    } catch(Exception $e) {
        // Rollback transaction on error
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->rollback();
        }
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
    // Close the connection (optional, but good practice)
    // $conn->close(); // db_connect.php already closes the connection at the end

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
