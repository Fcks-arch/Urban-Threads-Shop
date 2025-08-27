<?php
session_start();

// Check if admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.html');
    exit();
}

// Include the database connection file
require_once __DIR__ . 'db_connect.php'; // Adjust the path as needed

// Check if product ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = $_GET['id'];

    try {
        // Prepare a delete statement
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");

        // Bind parameters
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Product deleted successfully, redirect back to the dashboard
            header('Location: dashboard.php?message=Product deleted successfully!');
            exit();
        } else {
            // Error deleting product
            header('Location: dashboard.php?error=Error deleting product.');
            exit();
        }
    } catch (PDOException $e) {
        // Handle database error
        header('Location: dashboard.php?error=Database error: ' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Product ID not provided
    header('Location: dashboard.php?error=No product ID specified.');
    exit();
}
?> 