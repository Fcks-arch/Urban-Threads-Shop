<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_id = $_POST['cart_id'] ?? null;

    if (!$cart_id) {
        echo json_encode(['status' => 'error', 'message' => 'Missing cart ID']);
        exit;
    }

    // Ensure $conn is available and is a valid mysqli object
    if (!isset($conn) || !($conn instanceof mysqli)) {
         echo json_encode(['status' => 'error', 'message' => 'Database connection not available.']);
         exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else if ($stmt->affected_rows === 0) {
             echo json_encode(['status' => 'error', 'message' => 'Item not found or already removed.']);
        } else {
             throw new Exception('Execute failed: ' . $stmt->error);
        }
        $stmt->close();

    } catch(Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
// The connection is closed in db_connect.php after the script finishes.
?>
