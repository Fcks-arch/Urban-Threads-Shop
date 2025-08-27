<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // If not logged in as admin, redirect to admin login page
    header('Location: admin_login.html');
    exit();
}

// Include database connection
require_once 'db_connect.php';

// Ensure $conn is available from db_connect.php
if (!isset($conn) || $conn->connect_error) {
     die('Database connection failed: ' . $conn->connect_error); // Or handle error appropriately
}

// Check if product ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare a delete statement
    $sql = "DELETE FROM products WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            // Redirect back to the admin panel or products list after successful deletion
            header('Location: admin/adminpanel.php?section=products&status=deleted'); // Redirect to admin panel products section
            exit();
        } else {
            // Handle execution error
            error_log('Admin Product Deletion Error: ' . $stmt->error);
            die('Error deleting product.'); // Or display a user-friendly error
        }
        $stmt->close();
    } else {
        // Handle prepare error
        error_log('Admin Product Deletion Prepare Error: ' . $conn->error);
        die('Error preparing product deletion statement.'); // Or display a user-friendly error
    }
} else {
    // No product ID provided
    die('Invalid request: Product ID not specified.'); // Or display a user-friendly error
}

// Close database connection
$conn->close();

?> 