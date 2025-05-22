<?php
$host = 'localhost';
$dbname = 'loginusers';
$username = 'root';
$password = '';

// Create connection using MySQLi object-oriented style
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Check if it's an AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    } else {
        // For non-AJAX requests, display error directly
        die("Connection failed: " . $conn->connect_error);
    }
    exit();
}

// Set charset (equivalent to PDO's exec("SET NAMES utf8mb4"))
if (!$conn->set_charset("utf8mb4")) {
    // Handle error if setting charset fails
    // Depending on severity, you might just log this or output an error
    // For now, we'll just proceed, but in production you'd want to handle this.
    // You could add logging here if needed.
}

// Connection successful, $conn is ready to be used.
?> 