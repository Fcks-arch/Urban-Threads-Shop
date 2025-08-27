<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'User not logged in.'];

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch approved items for the logged-in user from the cart table
    // These are items that the admin has approved but the user hasn't been notified of yet (implicitly).
    // We fetch them from the cart table before they are potentially moved/deleted by the admin script.
    $sql_approved_items = "SELECT c.id as cart_id, c.product_name, c.quantity, c.price, c.tracking_number, p.image_url FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND c.status = 'approved'";

    if ($stmt_approved_items = $conn->prepare($sql_approved_items)) {
        $stmt_approved_items->bind_param("i", $user_id);
        $stmt_approved_items->execute();
        $result_approved_items = $stmt_approved_items->get_result();
        $approved_items = $result_approved_items->fetch_all(MYSQLI_ASSOC);
        $stmt_approved_items->close();

        $response = [
            'status' => 'success',
            'approved_items' => $approved_items
        ];

    } else {
        error_log('Fetch Approved Items Prepare Error: ' . $conn->error);
        $response = ['status' => 'error', 'message' => 'Error fetching approved items.'];
    }

    // Close database connection
    $conn->close();

} else {
     // If user is not logged in, return empty approved items
     $response = [
         'status' => 'success',
         'approved_items' => []
     ];
}

echo json_encode($response);
exit;
?> 