<?php
// This file handles updating product information.

session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // If not logged in as admin, redirect to admin login page
    header('Location: ../admin_login.html');
    exit();
}

// Include database connection
require_once '../db_connect.php';

// Ensure $conn is available from db_connect.php
if (!isset($conn) || $conn->connect_error) {
     die('Database connection failed: ' . $conn->connect_error); // Or handle error appropriately
}

$product = null;
$message = '';
$error = '';

// Get product ID from URL
$product_id = $_GET['id'] ?? null;

// Handle form submission for updating product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product_id) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock_quantity = trim($_POST['stock_quantity'] ?? '');
    // Handle image upload later if needed

    // Basic validation
    if (empty($name) || empty($description) || $price === '' || $stock_quantity === '') {
        $error = "Name, Description, Price, and Stock Quantity are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Invalid price.";
    } elseif (!filter_var($stock_quantity, FILTER_VALIDATE_INT) || $stock_quantity < 0) {
        $error = "Invalid stock quantity.";
    } else {
        // Prepare an update statement
        $sql_update = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ? WHERE id = ?";

        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssdii", $name, $description, $price, $stock_quantity, $product_id);

            if ($stmt_update->execute()) {
                $message = "Product updated successfully!";
                 // Redirect back to the admin panel after a short delay (optional)
                // header('Refresh: 2; URL=adminpanel.php#products');
                // exit();

            } else {
                 // Log MySQLi update error
                error_log('Admin Product Update Error: ' . $stmt_update->error);
                $error = "Error updating product: " . $stmt_update->error; // Display specific error for debugging
            }
            $stmt_update->close();
        } else {
             // Log MySQLi prepare error
            error_log('Admin Product Update Prepare Error: ' . $conn->error);
            $error = "Error preparing product update statement: " . $conn->error; // Display specific error
        }
    }
}

// Fetch product data to display in the form (for GET request or after POST)
if ($product_id) {
    $sql_fetch = "SELECT id, name, description, price, stock_quantity, image_url FROM products WHERE id = ? LIMIT 1";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $product_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows === 1) {
            $product = $result_fetch->fetch_assoc();
        } else {
            $error = "Product not found.";
        }
        $stmt_fetch->close();
    } else {
         // Log MySQLi prepare error for fetch
        error_log('Admin Product Fetch Prepare Error: ' . $conn->error);
        $error = "Error preparing product fetch statement: " . $conn->error; // Display specific error
    }
} else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     // Only show error if not a POST request attempting to update without ID
    $error = "No product ID specified for update.";
}

// Close database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product - Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css"> <!-- Link to the dashboard CSS -->
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h1>Admin Panel</h1>
        </div>
        <nav>
            <ul>
                <li><a href="adminpanel.php#users">Users</a></li>
                <li><a href="adminpanel.php#products">Products</a></li>
                <li><a href="adminpanel.php#administrators">Administrators</a></li>
                 <li><a href="adminpanel.php#pending-orders">Orders</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <header>
            <h2>Update Product</h2>
        </header>

        <div class="form-container">
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($product): ?>
                <form action="update_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">

                    <div class="form-group">
                        <label for="name">Product Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>
                    </div>

                     <div class="form-group">
                        <label for="stock_quantity">Stock Quantity:</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                    </div>

                    <div class="form-group">
                        <input type="submit" value="Update Product">
                    </div>
                </form>
            <?php elseif (!$product_id): ?>
                 <!-- Show this message only if no ID was provided in the URL -->
                <p>Please select a product to update from the admin panel.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 