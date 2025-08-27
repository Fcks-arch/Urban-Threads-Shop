<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Get cart items
        $stmt = $conn->prepare("SELECT c.*, p.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_items = $result->fetch_all(MYSQLI_ASSOC);

        if (empty($cart_items)) {
            throw new Exception('Cart is empty');
        }

        // Check stock and update product quantities
        foreach ($cart_items as $item) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception("Not enough stock for {$item['product_name']}");
            }

            // Update product stock
            $new_quantity = $item['stock_quantity'] - $item['quantity'];
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_quantity, $item['product_id']);
            $stmt->execute();
        }

        // Clear the cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?> 