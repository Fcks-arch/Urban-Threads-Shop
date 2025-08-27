<?php
ini_set('display_errors', 0); // Turn off display_errors for production
ini_set('log_errors', 1); // Log errors instead
ini_set('error_log', __DIR__ . '/error.log'); // Specify error log file
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
    $full_name = $_POST['full_name'] ?? ''; // Get full name from form
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($full_name) || empty($email) || empty($role) || empty($password) || empty($confirm_password)) {
        $response = ['status' => 'error', 'message' => 'All fields are required.'];
    } elseif ($password !== $confirm_password) {
        $response = ['status' => 'error', 'message' => 'Passwords do not match.'];
    } elseif (!in_array($role, ['admin', 'super_admin'])) {
         $response = ['status' => 'error', 'message' => 'Invalid role selected.'];
    } else {
        // Use MySQLi prepared statement to check if email or username already exists
        $sql = "SELECT email FROM admins WHERE email = ? OR username = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $email, $full_name);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Email or Username already exists.'];
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Use MySQLi prepared statement to Insert into admins table
                $sql = "INSERT INTO admins (username, email, password, role) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

                    if ($stmt->execute()) {
                         // Get the ID of the newly inserted admin
                        $new_admin_id = $stmt->insert_id;

                        $response = [
                            'status' => 'success',
                            'message' => 'Admin account created successfully!',
                            'redirect' => 'admin_login.html?registered=true' // Redirect to admin login page with success flag
                        ];
                    } else {
                         // Log the specific MySQLi error
                        error_log('MySQLi Insert Error: ' . $stmt->error);
                        $response = ['status' => 'error', 'message' => 'Registration failed: Database insert error.'];
                    }
                } else {
                     // Log the specific MySQLi error for prepare
                    error_log('MySQLi Prepare Error (Insert): ' . $conn->error);
                    $response = ['status' => 'error', 'message' => 'Registration failed: Database prepare error.'];
                }
            }
        } else {
             // Log the specific MySQLi error for prepare
            error_log('MySQLi Prepare Error (Select): ' . $conn->error);
            $response = ['status' => 'error', 'message' => 'Registration failed: Database prepare error.'];
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