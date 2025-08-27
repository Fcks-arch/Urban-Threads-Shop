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

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // Handle adding a new product
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? '';
        $stock_quantity = $_POST['stock_quantity'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $description = $_POST['description'] ?? '';

        // Basic validation
        if (empty($name) || empty($price) || empty($stock_quantity) || empty($image_url) || empty($description)) {
             $_SESSION['error_message'] = 'All fields are required.';
            header('Location: add_product.html');
            exit();
        }

        // Validate price and stock are numbers
        if (!is_numeric($price) || !is_numeric($stock_quantity) || $price < 0 || $stock_quantity < 0) {
             $_SESSION['error_message'] = 'Price and Stock Quantity must be non-negative numbers.';
            header('Location: add_product.html');
            exit();
        }

        try {
            // Insert the new product into the products table
            $stmt_insert = $conn->prepare("INSERT INTO products (name, price, stock_quantity, image_url, description) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param('sdiss', $name, $price, $stock_quantity, $image_url, $description);
            
            if ($stmt_insert->execute()) {
                $_SESSION['success_message'] = 'Product added successfully.';
                header('Location: adminpanel.php#products');
                exit();
            } else {
                throw new Exception("Error inserting product.");
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'An error occurred: ' . $e->getMessage();
            error_log('Admin Add Product Error: ' . $e->getMessage());
            header('Location: add_product.html');
            exit();
        }

    } elseif ($action === 'edit') {
        // Handle editing an existing product (will implement later)
        $_SESSION['error_message'] = 'Edit functionality not yet implemented.';
        header('Location: adminpanel.php#products');
        exit();

    } else {
        // Invalid action
        $_SESSION['error_message'] = 'Invalid action specified.';
        header('Location: adminpanel.php#products');
        exit();
    }

} else {
    // Not a POST request, redirect to dashboard
    header('Location: adminpanel.php#products');
    exit();
}
?> 