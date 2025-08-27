<?php
ini_set('display_errors', 0); // Turn off display_errors for production
ini_set('log_errors', 1); // Log errors instead
ini_set('error_log', __DIR__ . '/error_login.log'); // Specify error log file for login
error_reporting(E_ALL); // Report all errors

ob_start(); // Start output buffering

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json'); // Always return JSON

$response = ['status' => 'error', 'message' => 'Invalid request.'];

// Ensure $conn is available from db_connect.php
if (!isset($conn) || $conn->connect_error) {
     $response = ['status' => 'error', 'message' => 'Database connection failed.'];
     ob_end_clean();
     echo json_encode($response);
     exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'All fields are required.'];
    } else {
        try {
            // Use MySQLi prepared statement to authenticate against the admins table
            $sql = "SELECT id, username, email, password, role FROM admins WHERE email = ? LIMIT 1";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $admin_user = $result->fetch_assoc();

                    // Verify the password against the 'password' column
                    if (password_verify($password, $admin_user['password'])) {
                         // Check if the role is admin or super_admin
                        if ($admin_user['role'] === 'admin' || $admin_user['role'] === 'super_admin') {
                            // Admin login successful, set session variables
                            $_SESSION['user_id'] = $admin_user['id'];
                            $_SESSION['username'] = $admin_user['username'];
                            $_SESSION['role'] = $admin_user['role'];
                            $_SESSION['is_admin'] = true;

                            $response = [
                                'status' => 'success',
                                'message' => 'Welcome, ' . htmlspecialchars($admin_user['username']) . '!',
                                'redirect' => 'admin/adminpanel.php' // Redirect to admin dashboard
                            ];
                        } else {
                            // User found but role is not admin
                             $response = ['status' => 'error', 'message' => 'You do not have admin privileges.'];
                        }

                    } else {
                        // Password is not valid
                        $response = ['status' => 'error', 'message' => 'Invalid email or password.'];
                    }
                } else {
                    // User doesn't exist
                    $response = ['status' => 'error', 'message' => 'Invalid email or password.'];
                }
                $stmt->close();
            } else {
                 // Log MySQLi prepare error
                error_log('MySQLi Prepare Error (Login): ' . $conn->error);
                $response = ['status' => 'error', 'message' => 'Login failed: Database error.'];
            }

        } catch (Exception $e) {
             // Log any other exceptions
            error_log('Admin Login Error: ' . $e->getMessage());
             $response = ['status' => 'error', 'message' => 'An error occurred during login.'];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Invalid request method.'];
}

// Close database connection
$conn->close();

ob_end_clean(); // Clean (delete) the output buffer and turn off output buffering

echo json_encode($response);
exit; 