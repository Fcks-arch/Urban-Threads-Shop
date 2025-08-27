<?php
session_start();
require_once '../db_connect.php'; // Adjust path as necessary

header('Content-Type: application/json');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

// Function to generate a simple tracking number
function generateTrackingNumber() {
    // You can use a more sophisticated method for production
    return 'TRK' . strtoupper(bin2hex(random_bytes(4))); // Simple 8-character hex string prefixed with TRK
}

// Check for POST request and cart_id
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'] ?? null;

    if ($cart_id === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing cart item ID.']);
        exit();
    }

    // Ensure $conn is available
    if (!isset($conn) || $conn->connect_error) {
         echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
         exit;
    }

    // Generate a tracking number
    $tracking_number = generateTrackingNumber();

    // Update the status of the cart item to 'approved' and set the tracking number
    // Assumes 'tracking_number' column exists in the 'cart' table
    $sql_update = "UPDATE cart SET status = 'approved', tracking_number = ? WHERE id = ?";

    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("si", $tracking_number, $cart_id);

        if ($stmt_update->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Checkout request approved and tracking number generated.', 'tracking_number' => $tracking_number]);
            
        } else {
            error_log('Admin Approve Checkout Error: ' . $stmt_update->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to approve checkout request.']);
        }
        $stmt_update->close();

    } else {
        error_log('Admin Approve Checkout Prepare Error: ' . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare update statement.']);
    }

    // Close database connection
    $conn->close();

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

?> 