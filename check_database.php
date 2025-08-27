<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Database Structure Check</h2>";

try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Check users table structure
    echo "<h3>Users Table Structure:</h3>";
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Error describing users table: " . $conn->error . "</p>";
    }
    
    // Check if there are any users
    echo "<h3>Users in Database:</h3>";
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total users: " . $row['count'] . "</p>";
        
        if ($row['count'] > 0) {
            $users = $conn->query("SELECT id, full_name, email FROM users LIMIT 5");
            echo "<p>Sample users:</p><ul>";
            while ($user = $users->fetch_assoc()) {
                echo "<li>ID: " . $user['id'] . " - Name: " . $user['full_name'] . " - Email: " . $user['email'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>No users found in database!</p>";
            echo "<p><a href='user_signup.html'>Create a user account</a></p>";
        }
    }
    
    // Check orders table
    echo "<h3>Orders Table Structure:</h3>";
    $result = $conn->query("DESCRIBE orders");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Error describing orders table: " . $conn->error . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

$conn->close();

echo "<h3>Quick Actions:</h3>";
echo "<p><a href='simple_auth_test.php'>Test Authentication</a></p>";
echo "<p><a href='signin.html'>Go to Sign In</a></p>";
echo "<p><a href='signup.html'>Go to Sign Up</a></p>";
echo "<p><a href='shop2.html'>Go to Shop</a></p>";
?>

