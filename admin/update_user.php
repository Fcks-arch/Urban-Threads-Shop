<?php
// This file handles updating user information.

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

$user = null;
$message = '';
$error = '';

// Get user ID from URL
$user_id = $_GET['id'] ?? null;

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Password field is optional for update
    // Ensure no role is being processed for users

    // Basic validation
    if (empty($full_name) || empty($email)) {
        $error = "Full Name and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $error = "Invalid email format.";
    } else {
        // Check if email already exists for another user
        $sql_check_email = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";
        if ($stmt_check_email = $conn->prepare($sql_check_email)) {
            $stmt_check_email->bind_param("si", $email, $user_id);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();

            if ($stmt_check_email->num_rows > 0) {
                $error = "Error: Email already exists for another user.";
            } else {
                // Start building the SQL query for update (only full_name and email)
                $sql_update = "UPDATE users SET full_name = ?, email = ?";
                $params = [ $full_name, $email ];
                $param_types = "ss";

                // Add password hash to update query if password is provided
                // Assuming 'password_hash' column stores hashed passwords based on table structure
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql_update .= ", password_hash = ?";
                    $params[] = $hashed_password;
                    $param_types .= "s";
                }

                $sql_update .= " WHERE id = ?";
                $params[] = $user_id;
                $param_types .= "i";

                if ($stmt_update = $conn->prepare($sql_update)) {
                    // Use call_user_func_array to bind parameters dynamically
                    $bind_params = array_merge([$param_types], $params);
                     // The helper function ref_values is defined below the HTML
                    call_user_func_array([$stmt_update, 'bind_param'], ref_values($bind_params));

                    if ($stmt_update->execute()) {
                        $message = "User updated successfully!";
                         // Redirect back to the admin panel after a short delay (optional)
                        // header('Refresh: 2; URL=adminpanel.php#users');
                        // exit();

                    } else {
                         // Log MySQLi update error
                        error_log('Admin User Update Error: ' . $stmt_update->error);
                        $error = "Error updating user: " . $stmt_update->error; // Display specific error for debugging
                    }
                    $stmt_update->close();
                } else {
                     // Log MySQLi prepare error
                    error_log('Admin User Update Prepare Error: ' . $conn->error);
                    $error = "Error preparing user update statement: " . $conn->error; // Display specific error
                }
            }
            $stmt_check_email->close();
        } else {
             // Log MySQLi prepare error for email check
            error_log('Admin User Update Email Check Prepare Error: ' . $conn->error);
            $error = "Error preparing email check statement: " . $conn->error; // Display specific error
        }
    }
}

// Fetch user data to display in the form (for GET request or after POST)
if ($user_id) {
    // Select only columns that exist in the users table: id, full_name, email, created_at
    // Based on your provided table structure, 'role' and 'password_hash' are NOT in the users table for fetching here.
    // 'password_hash' is only needed for inserting/updating the password.
    $sql_fetch = "SELECT id, full_name, email FROM users WHERE id = ? LIMIT 1";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $user_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows === 1) {
            $user = $result_fetch->fetch_assoc();
        } else {
            $error = "User not found.";
        }
        $stmt_fetch->close();
    } else {
         // Log MySQLi prepare error for fetch
        error_log('Admin User Fetch Prepare Error: ' . $conn->error);
        $error = "Error preparing user fetch statement: " . $conn->error; // Display specific error
    }
} else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Only show error if not a POST request attempting to update without ID
    $error = "No user ID specified for update.";
}

// Helper function for dynamic bind_param
// Moved below the main PHP block as it's only used within the script
function ref_values($arr) {
    $refs = array();
    foreach ($arr as $key => $value) {
        $refs[$key] = & $arr[$key];
    }
    return $refs;
}

// Close database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User - Admin Dashboard</title>
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
            <h2>Update User</h2>
        </header>

        <div class="form-container">
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($user): ?>
                <form action="update_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">

                    <div class="form-group">
                        <label for="full_name">Full Name:</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password (leave blank if not changing):</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <?php /* Removed role field as it does not exist in the users table
                    <div class="form-group">
                         <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="super_admin" <?php echo ($user['role'] === 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                        </select>
                    </div>
                    */ ?>

                    <div class="form-group">
                        <input type="submit" value="Update User">
                    </div>
                </form>
            <?php elseif (!$user_id): ?>
                 <!-- Show this message only if no ID was provided in the URL -->
                <p>Please select a user to update from the admin panel.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 