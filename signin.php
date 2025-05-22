<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json'); // Always return JSON

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'All fields are required.'];
    } else {
        try {
            // Authenticate against the users table using email and password_hash
            $stmt = $conn->prepare("SELECT id, full_name, password_hash FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['full_name']; // Use full_name for username in session
                $_SESSION['is_admin'] = false; // Assume all users in this table are not admins by default

                $response = [
                    'status' => 'success',
                    'message' => 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!',
                    'redirect' => 'shop2.html' // Redirect to shop page
                ];

            } else {
                // Invalid credentials
                $response = ['status' => 'error', 'message' => 'Invalid email or password.'];
            }
        } catch (Exception $e) {
            // Catch any exceptions
            $response = ['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()];
            // Log the error for debugging
            error_log('Signin error: ' . $e->getMessage());
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Invalid request method.'];
}

echo json_encode($response);
exit;