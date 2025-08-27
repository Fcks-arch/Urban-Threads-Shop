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
    // Get selected cart item IDs from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $selectedCartIds = $data['cart_ids'] ?? [];

    if (empty($selectedCartIds)) {
        echo json_encode(['status' => 'error', 'message' => 'No items selected for checkout.']);
        exit;
    }

    // Ensure $conn is available
    if (!isset($conn) || $conn->connect_error) {
         echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
         exit;
    }

    // Validate that the selected cart items belong to the logged-in user
    $user_id = $_SESSION['user_id'];
    $placeholders = implode(',', array_fill(0, count($selectedCartIds), '?'));
    $sql_check = "SELECT COUNT(*) FROM cart WHERE user_id = ? AND id IN ($placeholders)";

    if ($stmt_check = $conn->prepare($sql_check)) {
        $types = 'i' . str_repeat('i', count($selectedCartIds));
        $params = array_merge([$user_id], $selectedCartIds);

        // Bind parameters for the check statement
        $bind_params_check = [];
        $bind_params_check[] = &$types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_params_check[] = &$params[$i];
        }
        call_user_func_array([$stmt_check, 'bind_param'], $bind_params_check);

        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count !== count($selectedCartIds)) {
            // This means some of the requested cart IDs don't belong to the user
            echo json_encode(['status' => 'error', 'message' => 'Invalid cart items selected.']);
            exit;
        }

        // Now, update the status of the selected cart items

        // Start transaction for the update
        $conn->begin_transaction();

        $sql_update = "UPDATE cart SET status = 'pending_checkout' WHERE user_id = ? AND id IN ($placeholders)";

        if ($stmt_update = $conn->prepare($sql_update)) {
             $types = 'i' . str_repeat('i', count($selectedCartIds));
             $params = array_merge([$user_id], $selectedCartIds);

            // Bind parameters for the update statement
            $bind_params_update = [];
            $bind_params_update[] = &$types;
            for ($i = 0; $i < count($params); $i++) {
                $bind_params_update[] = &$params[$i];
            }
            call_user_func_array([$stmt_update, 'bind_param'], $bind_params_update);

            if ($stmt_update->execute()) {
                 $conn->commit();
                 echo json_encode(['status' => 'success', 'message' => 'Checkout request submitted. Waiting for admin approval.']);
            } else {
                 $conn->rollback();
                 error_log('Checkout request update failed: ' . $stmt_update->error);
                 echo json_encode(['status' => 'error', 'message' => 'Failed to submit checkout request.']);
            }
             $stmt_update->close();

        } else {
            $conn->rollback();
            error_log('Checkout request update prepare failed: ' . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare update statement.']);
        }

    } else {
         error_log('Checkout request check prepare failed: ' . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare check statement.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

// Close database connection (assuming db_connect.php doesn't close it or explicitly close it here)
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?> 