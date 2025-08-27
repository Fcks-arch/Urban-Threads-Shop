<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $change = isset($_POST['change']) ? intval($_POST['change']) : 0;

    if (!$cart_id || !$change) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }

    // Ensure $conn is available and is a valid mysqli object
    if (!isset($conn) || !($conn instanceof mysqli)) {
         echo json_encode(['status' => 'error', 'message' => 'Database connection not available.']);
         exit;
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // Get current quantity
        $stmt = $conn->prepare("SELECT quantity FROM cart WHERE id = ? AND user_id = ?");
         if ($stmt === false) {
            throw new Exception('Prepare failed (get quantity): ' . $conn->error);
        }
        $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_item = $result->fetch_assoc();
         $stmt->close();

        if (!$cart_item) {
            throw new Exception('Cart item not found');
        }

        $new_quantity = $cart_item['quantity'] + $change;

        if ($new_quantity < 1) {
            // Delete item if quantity becomes 0 or negative
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
             if ($stmt === false) {
                throw new Exception('Prepare failed (delete item): ' . $conn->error);
            }
            $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
        } else {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
             if ($stmt === false) {
                throw new Exception('Prepare failed (update quantity): ' . $conn->error);
            }
            $stmt->bind_param("iii", $new_quantity, $cart_id, $_SESSION['user_id']);
        }
        
        $stmt->execute();
         if ($stmt->affected_rows < 0) { // Check for errors other than no rows affected
             throw new Exception('Execute failed: ' . $stmt->error);
         }
         $stmt->close();

        $conn->commit();

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
// The connection is closed in db_connect.php after the script finishes.
?> 