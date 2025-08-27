<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo "Email and password are required";
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, full_name, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                
                echo "<h2>Login Successful!</h2>";
                echo "<p>User ID: " . $user['id'] . "</p>";
                echo "<p>Name: " . $user['full_name'] . "</p>";
                echo "<p>Session ID: " . session_id() . "</p>";
                echo "<p><a href='simple_auth_test.php'>Check Session Status</a></p>";
                echo "<p><a href='checkout.html'>Test Checkout</a></p>";
                echo "<p><a href='shop2.html'>Go to Shop</a></p>";
            } else {
                echo "<h2>Login Failed</h2>";
                echo "<p>Invalid password</p>";
                echo "<p><a href='simple_auth_test.php'>Try Again</a></p>";
            }
        } else {
            echo "<h2>Login Failed</h2>";
            echo "<p>User not found</p>";
            echo "<p><a href='simple_auth_test.php'>Try Again</a></p>";
        }
        
    } catch (Exception $e) {
        echo "<h2>Error</h2>";
        echo "<p>Database error: " . $e->getMessage() . "</p>";
        echo "<p><a href='simple_auth_test.php'>Go Back</a></p>";
    }
    
    $conn->close();
} else {
    echo "Invalid request method";
}
?>

