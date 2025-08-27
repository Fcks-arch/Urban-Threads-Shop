<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Include database connection
include 'db_connect.php';

// Check if order ID is provided in the URL
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    // Get the order ID from the URL
    $order_id = trim($_GET['id']);

    // Prepare an update statement
    // Assuming the approved status is 'Approved', you can change this if your database uses a different value
    $sql_update = "UPDATE orders SET status = 'Approved' WHERE id = ?";

    if ($stmt_update = $conn->prepare($sql_update)) {
        // Bind variables to the prepared statement as parameters
        $stmt_update->bind_param("i", $order_id);

        // Attempt to execute the prepared statement
        if ($stmt_update->execute()) {
            // Order status updated successfully. Redirect back to the orders section on the dashboard.
            header("location: dashbord.php#orders-section");
            exit();
        } else {
            echo "Error updating order status: " . $conn->error; // Display specific error for debugging
        }

        // Close statement
        $stmt_update->close();
    }
} else {
    // Order ID not provided or is empty. Redirect back to the dashboard or display an error.
    header("location: dashbord.php#orders-section"); // Redirect back to dashboard orders section
    exit();
}

// Close database connection
$conn->close();

?> 