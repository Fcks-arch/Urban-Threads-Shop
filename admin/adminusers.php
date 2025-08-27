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



// Fetch pending checkout requests from the cart table


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

    </script>
</body>
</html>
