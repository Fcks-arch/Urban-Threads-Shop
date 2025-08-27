<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // If not logged in as admin, redirect to admin login page
    header('Location: ../admin_login.html');
    exit();
}

// Include database connection
require_once '../db_connect.php';

// Ensure $conn is available from db_connect.php
if (!isset($conn) || $conn->connect_error) {
     die('Database connection failed: ' . $conn->connect_error); // Or handle error appropriately
}

// Check if order ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $order_id = $_GET['id'];

    // Prepare an update statement
    // Assuming 'status' column exists in 'orders' table
    $sql = "UPDATE orders SET status = 'approved' WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            // Redirect back to the admin panel after successful approval
            header('Location: adminpanel.php?status=approved');
            exit();
        } else {
            // Handle execution error
            error_log('Admin Order Approval Error: ' . $stmt->error);
            die('Error approving order.'); // Or display a user-friendly error
        }
        $stmt->close();
    } else {
        // Handle prepare error
        error_log('Admin Order Approval Prepare Error: ' . $conn->error);
        die('Error preparing order approval statement.'); // Or display a user-friendly error
    }
} else {
    // No order ID provided
    die('Invalid request: Order ID not specified.'); // Or display a user-friendly error
}

// Close database connection
$conn->close();

?> 