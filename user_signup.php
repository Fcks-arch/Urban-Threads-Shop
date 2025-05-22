<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json'); // Always return JSON

$response = ['status' => 'error', 'message' => 'Invalid request method.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $response = ['status' => 'error', 'message' => 'All fields are required'];
    } elseif ($password !== $confirm_password) {
        $response = ['status' => 'error', 'message' => 'Passwords do not match'];
    } else {
        try {
            // Check if email already exists in the users table
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing_user = $result->fetch_assoc();

            if ($existing_user) {
                $response = ['status' => 'error', 'message' => 'Email already exists'];
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into users table with full_name and password_hash columns
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $full_name, $email, $hashed_password);
                
                if ($stmt->execute()) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Registration successful! Please sign in.',
                        'redirect' => 'signin.html?registered=true'
                    ];
                } else {
                    throw new Exception("Error executing statement");
                }
            }
        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()];
            error_log('Signup error: ' . $e->getMessage());
        }
    }
}

echo json_encode($response);
exit; 