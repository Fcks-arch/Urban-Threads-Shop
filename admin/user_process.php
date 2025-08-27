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
        // Handle adding a new user
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($full_name) || empty($email) || empty($password)) {
            // Handle missing fields
            $_SESSION['error_message'] = 'All fields are required.';
            header('Location: add_user.html');
            exit();
        }

        try {
            // Check if email already exists in the users table
            $stmt_check = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt_check->bind_param('s', $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $_SESSION['error_message'] = 'User with this email already exists.';
                header('Location: add_user.html');
                exit();
            }
            $stmt_check->close();

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the users table
            $stmt_insert = $conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
            $stmt_insert->bind_param('sss', $full_name, $email, $hashed_password);

            if ($stmt_insert->execute()) {
                $_SESSION['success_message'] = 'User added successfully.';
                header('Location: dashboard.php#users');
                exit();
            } else {
                throw new Exception("Error inserting user: " . $conn->error);
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'An error occurred: ' . $e->getMessage();
            error_log('Admin Add User Error: ' . $e->getMessage());
            header('Location: add_user.html');
            exit();
        }

    } elseif ($action === 'edit') {
        // Handle editing an existing user (will implement later)
        $_SESSION['error_message'] = 'Edit functionality not yet implemented.';
        header('Location: dashboard.php#users');
        exit();

    } else {
        // Invalid action
        $_SESSION['error_message'] = 'Invalid action specified.';
        header('Location: dashboard.php#users');
        exit();
    }

} else {
    // Not a POST request, redirect to dashboard
    header('Location: dashboard.php#users');
    exit();
}
?> 