<?php
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

// Fetch all users (excluding admins from the admins table if you have one, or filter by role if using users table)
// Assuming users are in the `users` table and not admins based on our latest create_tables.sql
// If you need to display admins from the `admins` table here, let me know.
try {
    $users = []; // Initialize users array
    $sql_users = "SELECT id, full_name, email, created_at FROM users";
    if ($stmt_users = $conn->prepare($sql_users)) {
        $stmt_users->execute();
        $result_users = $stmt_users->get_result();
        $users = $result_users->fetch_all(MYSQLI_ASSOC);
        $stmt_users->close();
    } else {
        throw new mysqli_sql_exception('Prepare failed: ' . $conn->error);
    }
} catch (mysqli_sql_exception $e) {
    $users = []; // Empty array on error
    error_log('Admin Dashboard User Fetch Error: ' . $e->getMessage());
    // Optionally set an error message to display on the dashboard
    $user_fetch_error = 'Error loading users: ' . $e->getMessage();
} catch (Exception $e) {
     $users = []; // Empty array on error
     error_log('Admin Dashboard User Fetch Generic Error: ' . $e->getMessage());
     $user_fetch_error = 'An unexpected error occurred while loading users.';
}

// Fetch all products, including price and description
try {
    $products = []; // Initialize products array
    $sql_products = "SELECT id, name, price, description, stock_quantity, image_url FROM products";
    if ($stmt_products = $conn->prepare($sql_products)) {
        $stmt_products->execute();
        $result_products = $stmt_products->get_result();
        $products = $result_products->fetch_all(MYSQLI_ASSOC);
        $stmt_products->close();
    } else {
        throw new mysqli_sql_exception('Prepare failed: ' . $conn->error);
    }
} catch (mysqli_sql_exception $e) {
    $products = []; // Empty array on error
    error_log('Admin Dashboard Product Fetch Error: ' . $e->getMessage());
    // Optionally set an error message to display on the dashboard
    $product_fetch_error = 'Error loading products: ' . $e->getMessage();
} catch (Exception $e) {
     $products = []; // Empty array on error
     error_log('Admin Dashboard Product Fetch Generic Error: ' . $e->getMessage());
     $product_fetch_error = 'An unexpected error occurred while loading products.';
}

// Fetch all administrators from the admins table
try {
    $admins = []; // Initialize admins array
    $sql_admins = "SELECT id, username, email, role, created_at FROM admins";
    if ($stmt_admins = $conn->prepare($sql_admins)) {
        $stmt_admins->execute();
        $result_admins = $stmt_admins->get_result();
        $admins = $result_admins->fetch_all(MYSQLI_ASSOC);
        $stmt_admins->close();
    } else {
        throw new mysqli_sql_exception('Prepare failed: ' . $conn->error);
    }
} catch (mysqli_sql_exception $e) {
    $admins = []; // Empty array on error
    error_log('Admin Dashboard Admin Fetch Error: ' . $e->getMessage());
    // Optionally set an error message to display on the dashboard
    $admin_fetch_error = 'Error loading administrators: ' . $e->getMessage();
} catch (Exception $e) {
     $admins = []; // Empty array on error
     error_log('Admin Dashboard Admin Fetch Generic Error: ' . $e->getMessage());
     $admin_fetch_error = 'An unexpected error occurred while loading administrators.';
}

// Fetch pending orders
try {
    $pending_orders_from_orders_table = []; // Initialize pending orders array from the 'orders' table
    // Assuming 'status' column exists in 'orders' table
    $sql_pending_orders_from_orders_table = "SELECT id, user_id, status, created_at FROM orders WHERE status = 'pending'";
    if ($stmt_pending_orders_from_orders_table = $conn->prepare($sql_pending_orders_from_orders_table)) {
        $stmt_pending_orders_from_orders_table->execute();
        $result_pending_orders_from_orders_table = $stmt_pending_orders_from_orders_table->get_result();
        $pending_orders_from_orders_table = $result_pending_orders_from_orders_table->fetch_all(MYSQLI_ASSOC);
        $stmt_pending_orders_from_orders_table->close();
    } else {
        throw new mysqli_sql_exception('Prepare failed: ' . $conn->error);
    }
} catch (mysqli_sql_exception $e) {
    $pending_orders_from_orders_table = []; // Empty array on error
    error_log('Admin Dashboard Pending Orders Fetch Error: ' . $e->getMessage());
    $pending_orders_from_orders_table_error = 'Error loading pending orders: ' . $e->getMessage();
} catch (Exception $e) {
     $pending_orders_from_orders_table = []; // Empty array on error
     error_log('Admin Dashboard Pending Orders Fetch Generic Error: ' . $e->getMessage());
     $pending_orders_from_orders_table_error = 'An unexpected error occurred while loading pending orders.';
}

// Fetch pending checkout requests from the cart table
try {
    $pending_checkout_requests = []; // Initialize pending checkout requests array
    $sql_pending_checkout = "SELECT c.id as cart_id, c.quantity, c.price, c.created_at, u.full_name as user_name, p.name as product_name, p.image_url FROM cart c JOIN users u ON c.user_id = u.id JOIN products p ON c.product_id = p.id WHERE c.status = 'pending_checkout'";
    if ($stmt_pending_checkout = $conn->prepare($sql_pending_checkout)) {
        $stmt_pending_checkout->execute();
        $result_pending_checkout = $stmt_pending_checkout->get_result();
        $pending_checkout_requests = $result_pending_checkout->fetch_all(MYSQLI_ASSOC);
        $stmt_pending_checkout->close();
    } else {
        throw new mysqli_sql_exception('Prepare failed: ' . $conn->error);
    }
} catch (mysqli_sql_exception $e) {
    $pending_checkout_requests = []; // Empty array on error
    error_log('Admin Dashboard Pending Checkout Fetch Error: ' . $e->getMessage());
    $pending_checkout_requests_error = 'Error loading pending checkout requests: ' . $e->getMessage();
} catch (Exception $e) {
     $pending_checkout_requests = []; // Empty array on error
     error_log('Admin Dashboard Pending Checkout Fetch Generic Error: ' . $e->getMessage());
     $pending_checkout_requests_error = 'An unexpected error occurred while loading pending checkout requests.';
}

// Fetch approved checkout requests from the cart table
try {
    $approved_checkout_requests = []; // Initialize approved checkout requests array
    // Assuming 'tracking_number' column exists in the cart table
    $sql_approved_checkout = "SELECT c.id as cart_id, c.quantity, c.price, c.created_at, c.tracking_number, u.full_name as user_name, p.name as product_name, p.image_url FROM cart c JOIN users u ON c.user_id = u.id JOIN products p ON c.product_id = p.id WHERE c.status = 'approved'";
    if ($stmt_approved_checkout = $conn->prepare($sql_approved_checkout)) {
        $stmt_approved_checkout->execute();
        $result_approved_checkout = $stmt_approved_checkout->get_result();
        $approved_checkout_requests = $result_approved_checkout->fetch_all(MYSQLI_ASSOC);
        $stmt_approved_checkout->close();
    } else {
        throw new mysqli_sql_exception('Prepare failed: ' . $conn->error);
    }
} catch (mysqli_sql_exception $e) {
    $approved_checkout_requests = []; // Empty array on error
    error_log('Admin Dashboard Approved Checkout Fetch Error: ' . $e->getMessage());
    $approved_checkout_requests_error = 'Error loading approved checkout requests: ' . $e->getMessage();
} catch (Exception $e) {
     $approved_checkout_requests = []; // Empty array on error
     error_log('Admin Dashboard Approved Checkout Fetch Generic Error: ' . $e->getMessage());
     $approved_checkout_requests_error = 'An unexpected error occurred while loading approved checkout requests.';
}

// Close the database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css"> <!-- Link to the dashboard CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h1>Admin Panel</h1>
        </div>
        <nav>
            <ul>
                <li><a href="#users">Users</a></li>
                <li><a href="#products">Products</a></li>
                <li><a href="#administrators">Administrators</a></li>
                <li><a href="#pending-checkout-requests">Pending Checkouts</a></li>
                <li><a href="#orders">Orders</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <h2>Dashboard</h2>
            <!-- Search Bar -->
            <input type="text" id="dashboardSearchBar" class="search-bar" placeholder="Search users, products, or admins...">
        </div>

        <!-- Users Section -->
        <div id="users" class="dashboard-section">
            <h3>Registered Users <a href="add_user.html" class="add-new-button">+ Add New User</a></h3>
            <?php if (isset($user_fetch_error)): ?>
                <p style="color: red;"><?php echo $user_fetch_error; ?></p>
            <?php elseif (empty($users)): ?>
                <p>No registered users found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Registered At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td class="actions">
                                    <a href="update_user.php?id=<?php echo $user['id']; ?>" class="edit-user">Update</a> |
                                    <a href="../delete_user.php?id=<?php echo $user['id']; ?>" class="delete-user" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Products Section -->
        <div id="products" class="dashboard-section">
            <h3>Available Products <a href="add_product.html" class="add-new-button">+ Add New Product</a></h3>
             <?php if (isset($product_fetch_error)): ?>
                <p style="color: red;"><?php echo $product_fetch_error; ?></p>
            <?php elseif (empty($products)): ?>
                <p>No products found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Picture</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                <td><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></td>
                                <td class="actions">
                                     <a href="update_product.php?id=<?php echo $product['id']; ?>" class="edit-product">Update</a> |
                                    <a href="../delete_product.php?id=<?php echo $product['id']; ?>" class="delete-product" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

         <!-- Administrators Section -->
        <div id="administrators" class="dashboard-section">
            <h3>Administrators <a href="admin/add_admin.html" class="add-new-button">+ Add New Administrator</a></h3>
            <?php if (isset($admin_fetch_error)): ?>
                <p style="color: red;"><?php echo $admin_fetch_error; ?></p>
            <?php elseif (empty($admins)): ?>
                <p>No administrators found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['id']); ?></td>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo htmlspecialchars($admin['role']); ?></td>
                                <td><?php echo htmlspecialchars($admin['created_at']); ?></td>
                                 <td class="actions">
                                     <a href="update_admin.php?id=<?php echo $admin['id']; ?>" class="edit-admin">Update</a> |
                                    <a href="../delete_admin.php?id=<?php echo $admin['id']; ?>" class="delete-admin" onclick="return confirm('Are you sure you want to delete this administrator?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Pending Orders Section -->
        <div id="pending-orders" class="dashboard-section">
            <h3>Pending Orders <a href="add_order.html" class="add-new-button">+ Add New Order</a></h3>
            <?php if (isset($pending_orders_from_orders_table_error)): ?>
                <p style="color: red;"><?php echo $pending_orders_from_orders_table_error; ?></p>
            <?php elseif (empty($pending_orders_from_orders_table)): ?>
                <p>No pending orders found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_orders_from_orders_table as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td class="actions">
                                    <a href="update_order.php?id=<?php echo $order['id']; ?>" class="edit-order">Update</a> |
                                    <a href="../delete_order.php?id=<?php echo $order['id']; ?>" class="delete-order" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a> |
                                    <a href="approve_order.php?id=<?php echo $order['id']; ?>" class="approve-order">Approve</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Pending Checkout Requests Section -->
        <div id="pending-checkout-requests" class="dashboard-section">
            <h3>Pending Checkout Requests</h3>
            <?php if (isset($pending_checkout_requests_error)): ?>
                <p style="color: red;"><?php echo $pending_checkout_requests_error; ?></p>
            <?php elseif (empty($pending_checkout_requests)): ?>
                <p>No pending checkout requests found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Requested At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_checkout_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['product_name']); ?></td>
                                <td><img src="../<?php echo htmlspecialchars($request['image_url']); ?>" alt="<?php echo htmlspecialchars($request['product_name']); ?>"></td>
                                <td><?php echo htmlspecialchars($request['quantity']); ?></td>
                                <td>₱<?php echo htmlspecialchars(number_format($request['price'] * $request['quantity'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                <td class="actions">
                                     <a href="#" class="approve-request" data-cart-id="<?php echo $request['cart_id']; ?>">Approve</a> |
                                    <a href="#" class="reject-request" data-cart-id="<?php echo $request['cart_id']; ?>">Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Approved Checkout Requests Section -->
        <div id="approved-checkout-requests" class="dashboard-section">
            <h3>Approved Checkout Requests</h3>
            <?php if (isset($approved_checkout_requests_error)): ?>
                <p style="color: red;"><?php echo $approved_checkout_requests_error; ?></p>
            <?php elseif (empty($approved_checkout_requests)): ?>
                <p>No approved checkout requests found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Approved At</th>
                             <th>Tracking Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved_checkout_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['product_name']); ?></td>
                                <td><img src="../<?php echo htmlspecialchars($request['image_url']); ?>" alt="<?php echo htmlspecialchars($request['product_name']); ?>"></td>
                                <td><?php echo htmlspecialchars($request['quantity']); ?></td>
                                <td>₱<?php echo htmlspecialchars(number_format($request['price'] * $request['quantity'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                 <td><?php echo htmlspecialchars($request['tracking_number']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Orders Section -->
        <div id="orders" class="dashboard-section">
            <h3>All Orders <a href="#" class="add-new-button">+ Add New Order</a></h3>
            <?php if (isset($pending_orders_from_orders_table_error)): ?>
                <p style="color: red;"><?php echo $pending_orders_from_orders_table_error; ?></p>
            <?php elseif (empty($pending_orders_from_orders_table)): ?>
                <p>No pending orders found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_orders_from_orders_table as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td class="actions">
                                    <a href="update_order.php?id=<?php echo $order['id']; ?>" class="edit-order">Update</a> |
                                    <a href="../delete_order.php?id=<?php echo $order['id']; ?>" class="delete-order" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a> |
                                    <a href="approve_order.php?id=<?php echo $order['id']; ?>" class="approve-order">Approve</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // Basic client-side filtering (for demonstration)
        const searchBar = document.getElementById('dashboardSearchBar');
        searchBar.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Filter Users Table
            const userTableBody = document.querySelector('#users .data-table tbody');
            if (userTableBody) {
                 const userRows = userTableBody.querySelectorAll('tr');
                 userRows.forEach(row => {
                     const text = row.textContent.toLowerCase();
                     row.style.display = text.includes(searchTerm) ? '' : 'none';
                 });
            }

            // Filter Products Table
            const productTableBody = document.querySelector('#products .data-table tbody');
             if (productTableBody) {
                const productRows = productTableBody.querySelectorAll('tr');
                productRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
             }

             // Filter Administrators Table
             const adminTableBody = document.querySelector('#administrators .data-table tbody');
             if (adminTableBody) {
                 const adminRows = adminTableBody.querySelectorAll('tr');
                 adminRows.forEach(row => {
                     const text = row.textContent.toLowerCase();
                     row.style.display = text.includes(searchTerm) ? '' : 'none';
                 });
             }
        });

         // Smooth scroll for sidebar navigation (optional)
         document.querySelectorAll('.sidebar nav a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Basic Delete Confirmation (client-side)
        // The actual deletion will be handled by separate PHP scripts (delete_user.php, delete_product.php, delete_admin.php)
        // These PHP scripts will need to be created.

        // Handle Approve Request clicks
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('approve-request')) {
                event.preventDefault(); // Prevent the default link behavior

                const cartId = event.target.dataset.cartId;

                if (confirm('Are you sure you want to approve this checkout request?')) {
                    // Send an AJAX request to the approve_checkout.php script
                    fetch('approve_checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'cart_id=' + encodeURIComponent(cartId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Visually remove the row from the table on success
                            const row = event.target.closest('tr');
                            if (row) {
                                row.remove();
                            }
                            // Optionally show a success message
                            alert(data.message);
                        } else {
                            // Show an error message
                            alert('Error: ' + (data.message || 'Failed to approve.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing the request.');
                    });
                }
            }
             // You would add similar logic here for 'reject-request'
        });

    </script>
</body>
</html>
