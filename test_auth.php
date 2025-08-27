<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Authentication Test</h2>";

// Start session
session_start();

echo "<h3>Session Information:</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

echo "<h3>Session Variables:</h3>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'>✗ No session variables found</p>";
} else {
    echo "<ul>";
    foreach ($_SESSION as $key => $value) {
        echo "<li><strong>$key:</strong> " . (is_array($value) ? json_encode($value) : $value) . "</li>";
    }
    echo "</ul>";
}

echo "<h3>Testing Database Connection:</h3>";
try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p><strong>Users in database:</strong> " . $row['count'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Error querying users table: " . $conn->error . "</p>";
    }
    
    // Test if there are any users with sessions
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p style='color: green;'>✓ User found in database:</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $user['id'] . "</li>";
            echo "<li><strong>Name:</strong> " . $user['full_name'] . "</li>";
            echo "<li><strong>Email:</strong> " . $user['email'] . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ User ID " . $user_id . " not found in database</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h3>Testing check_login.php:</h3>";
try {
    $response = file_get_contents('http://localhost/E-commerce%20Website/check_login.php');
    if ($response !== false) {
        $data = json_decode($response, true);
        echo "<p><strong>check_login.php response:</strong> " . json_encode($data) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Could not access check_login.php</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing check_login.php: " . $e->getMessage() . "</p>";
}

echo "<h3>Quick Actions:</h3>";
echo "<p><a href='signin.html'>Go to Sign In</a></p>";
echo "<p><a href='signup.html'>Go to Sign Up</a></p>";
echo "<p><a href='shop2.html'>Go to Shop</a></p>";
echo "<p><a href='checkout.html'>Go to Checkout</a></p>";

echo "<h3>Debug Information:</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
?>

