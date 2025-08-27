<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Fixing Database Structure</h2>";

try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Check if users table exists and has the correct structure
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
        
        if (!isset($columns['full_name'])) {
            echo "<p>Adding full_name column...</p>";
            $conn->query("ALTER TABLE users ADD COLUMN full_name VARCHAR(255) AFTER id");
            $needsUpdate = true;
        }
        
        if (!isset($columns['password_hash'])) {
            echo "<p>Adding password_hash column...</p>";
            $conn->query("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) AFTER email");
            $needsUpdate = true;
        }
        
        if ($needsUpdate) {
            echo "<p style='color: green;'>✓ Table structure updated!</p>";
        } else {
            echo "<p style='color: green;'>✓ Table structure is already correct!</p>";
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
        
        // Create the table with the correct structure
        echo "<p>Creating users table with correct structure...</p>";
        $sql = "CREATE TABLE users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            username VARCHAR(255),
            password VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✓ Users table created successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating users table: " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
echo "<p><a href='signup.html'>Go back to signup page</a></p>";
?>
