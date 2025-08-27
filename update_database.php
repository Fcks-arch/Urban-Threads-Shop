<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Updating Database for User Profile System</h2>";

try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Users table exists!</p>";
        
        // Check current table structure
        $result = $conn->query("DESCRIBE users");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row;
        }
        
        echo "<h3>Current table structure:</h3>";
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
        
        // Check if we need to add missing columns
        $needsUpdate = false;
        
        if (!isset($columns['phone'])) {
            echo "<p>Adding phone column...</p>";
            $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email");
            $needsUpdate = true;
        }
        
        if (!isset($columns['address'])) {
            echo "<p>Adding address column...</p>";
            $conn->query("ALTER TABLE users ADD COLUMN address TEXT AFTER phone");
            $needsUpdate = true;
        }
        
        if ($needsUpdate) {
            echo "<p style='color: green;'>✓ Table structure updated!</p>";
        } else {
            echo "<p style='color: green;'>✓ Table structure is already up to date!</p>";
        }
        
        // Show final table structure
        $result = $conn->query("DESCRIBE users");
        echo "<h3>Final table structure:</h3>";
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
        echo "<p style='color: red;'>✗ Users table does not exist!</p>";
        echo "<p>Please run the fix_database.php script first to create the basic table structure.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
echo "<p><a href='user_profile.html'>Go to User Profile</a></p>";
echo "<p><a href='home.html'>Go Home</a></p>";
?>
