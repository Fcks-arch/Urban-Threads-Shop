<?php
session_start();

// Check if admin is logged in and has super_admin role, otherwise redirect
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true || $_SESSION['role'] !== 'super_admin') {
    // Redirect to login or a page indicating insufficient privileges
    header('Location: admin_login.html?error=Insufficient privileges.');
    exit();
}

// Include the database connection file
require_once __DIR__ . '/../db_connect.php'; // Adjust the path as needed

// Check if admin ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $admin_id = $_GET['id'];

    // Prevent a super_admin from deleting themselves
    if ($_SESSION['user_id'] == $admin_id) {
        header('Location: dashboard.php?error=You cannot delete your own account.');
        exit();
    }

    try {
        // Prepare a delete statement for the admins table
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = :id");

        // Bind parameters
        $stmt->bindParam(':id', $admin_id, PDO::PARAM_INT);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Admin deleted successfully, redirect back to the dashboard
            header('Location: dashboard.php?message=Administrator deleted successfully!');
            exit();
        } else {
            // Error deleting admin
            header('Location: dashboard.php?error=Error deleting administrator.');
            exit();
        }
    } catch (PDOException $e) {
        // Handle database error
        header('Location: dashboard.php?error=Database error: ' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Admin ID not provided
    header('Location: dashboard.php?error=No administrator ID specified.');
    exit();
}
?> 