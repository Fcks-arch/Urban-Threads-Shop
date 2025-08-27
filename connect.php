<?php
$host = 'localhost';
$dbname = 'loginusers';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch(Exception $e) {
    // Log error (in production, use proper error logging)
    error_log("Database connection error: " . $e->getMessage());
    
    // If it's an AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection error']);
        exit;
    }
    
    // Otherwise, show a user-friendly message
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}
?> 