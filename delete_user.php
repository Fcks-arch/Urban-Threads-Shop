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

// Check if user ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prepare a delete statement
    $sql = "DELETE FROM users WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // Redirect back to the admin panel or users list after successful deletion
            header('Location: admin/adminpanel.php?section=users&status=deleted'); // Redirect to admin panel users section
            exit();
        } else {
            // Handle execution error
            error_log('Admin User Deletion Error: ' . $stmt->error);
            die('Error deleting user.'); // Or display a user-friendly error
        }
        $stmt->close();
    } else {
        // Handle prepare error
        error_log('Admin User Deletion Prepare Error: ' . $conn->error);
        die('Error preparing user deletion statement.'); // Or display a user-friendly error
    }
} else {
    // No user ID provided
    die('Invalid request: User ID not specified.'); // Or display a user-friendly error
}

// Close database connection
$conn->close();

?> 