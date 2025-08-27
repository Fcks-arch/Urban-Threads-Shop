<?php
// Make sure no output before session_start
session_start();

// Set headers
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Auth Debug</title></head><body>";
echo "<h1>Authentication Debug</h1>";

echo "<h2>1. Session Status</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

echo "<h2>2. Session Variables</h2>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'>No session variables found</p>";
} else {
    echo "<ul>";
    foreach ($_SESSION as $key => $value) {
        echo "<li><strong>$key:</strong> " . (is_array($value) ? json_encode($value) : $value) . "</li>";
    }
    echo "</ul>";
}

echo "<h2>3. Test check_login.php</h2>";
try {
    // Test the check_login.php directly
    ob_start();
    include 'check_login.php';
    $output = ob_get_clean();
    echo "<p><strong>Raw output:</strong> " . htmlspecialchars($output) . "</p>";
    
    $data = json_decode($output, true);
    if ($data) {
        echo "<p><strong>Parsed JSON:</strong> " . json_encode($data) . "</p>";
        echo "<p><strong>Logged in:</strong> " . ($data['logged_in'] ? 'YES' : 'NO') . "</p>";
    } else {
        echo "<p style='color: red;'>Failed to parse JSON</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Database Test</h2>";
try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>Database connection successful</p>";
    
    // Check users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total users in database: " . $row['count'] . "</p>";
        
        // Show some user details
        $users = $conn->query("SELECT id, full_name, email FROM users LIMIT 5");
        if ($users && $users->num_rows > 0) {
            echo "<p>Sample users:</p><ul>";
            while ($user = $users->fetch_assoc()) {
                echo "<li>ID: " . $user['id'] . " - Name: " . $user['full_name'] . " - Email: " . $user['email'] . "</li>";
            }
            echo "</ul>";
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Test Authentication Flow</h2>";
echo "<p><a href='signin.html' target='_blank'>Open Sign In Page</a></p>";
echo "<p><a href='signup.html' target='_blank'>Open Sign Up Page</a></p>";

echo "<h2>6. Manual Session Test</h2>";
if (isset($_GET['test_session'])) {
    $_SESSION['test_var'] = 'test_value_' . time();
    echo "<p style='color: green;'>Test session variable set: " . $_SESSION['test_var'] . "</p>";
    echo "<p><a href='debug_auth.php'>Refresh to see session variable</a></p>";
} else {
    echo "<p><a href='debug_auth.php?test_session=1'>Click to test session functionality</a></p>";
}

echo "<h2>7. Quick Actions</h2>";
echo "<p><a href='signin.html'>Go to Sign In</a></p>";
echo "<p><a href='shop2.html'>Go to Shop</a></p>";
echo "<p><a href='checkout.html'>Go to Checkout</a></p>";

echo "</body></html>";
?>

