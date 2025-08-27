<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Fixing Orders Database Table Structure</h2>";

try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Drop existing orders table if it exists
    $drop_orders = "DROP TABLE IF EXISTS order_items";
    if ($conn->query($drop_orders)) {
        echo "<p style='color: green;'>✓ Dropped existing order_items table</p>";
    }
    
    $drop_orders = "DROP TABLE IF EXISTS orders";
    if ($conn->query($drop_orders)) {
        echo "<p style='color: green;'>✓ Dropped existing orders table</p>";
    }
    
    // Create new orders table with correct structure
    $orders_table = "CREATE TABLE orders (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        shipping_method VARCHAR(50) NOT NULL,
        special_instructions TEXT,
        shipping_cost DECIMAL(10,2) DEFAULT 0.00,
        voucher_discount DECIMAL(10,2) DEFAULT 0.00,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        tracking_number VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($orders_table)) {
        echo "<p style='color: green;'>✓ New orders table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating orders table: " . $conn->error . "</p>";
    }
    
    // Create order_items table
    $order_items_table = "CREATE TABLE order_items (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT(11) NOT NULL,
        image_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($order_items_table)) {
        echo "<p style='color: green;'>✓ Order items table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating order items table: " . $conn->error . "</p>";
    }
    
    // Show new table structures
    echo "<h3>New Orders Table Structure:</h3>";
    $result = $conn->query("DESCRIBE orders");
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
    
    echo "<h3>New Order Items Table Structure:</h3>";
    $result = $conn->query("DESCRIBE order_items");
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
echo "<p><a href='checkout.html'>Go to Checkout</a></p>";
echo "<p><a href='shop2.html'>Go to Shop</a></p>";
?>
