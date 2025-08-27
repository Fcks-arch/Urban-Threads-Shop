<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>

        <?php
        // Include database connection
        include 'db_connect.php';

        $user = null;

        // Check if ID is provided in the URL
        if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
            // Prepare a select statement
            $sql = "SELECT id, full_name, email FROM users WHERE id = ?";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("i", $param_id);

                // Set parameters
                $param_id = trim($_GET['id']);

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    $result = $stmt->get_result();

                    if ($result->num_rows == 1) {
                        // Fetch result row as an associative array
                        $user = $result->fetch_assoc();
                    } else {
                        // URL doesn't contain valid id. Redirect to error page or user list.
                        echo "<div class='alert alert-danger'>User not found.</div>";
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        } else {
            // URL doesn't contain id parameter. Redirect to error page or user list.
            echo "<div class='alert alert-danger'>Invalid request.</div>";
        }

        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get hidden input value for user ID
            $user_id = $_POST['user_id'];
            $new_full_name = trim($_POST['full_name']);
            $new_email = trim($_POST['email']);

            // Validate inputs (add more robust validation as needed)
            if (empty($new_full_name)) {
                $name_err = "Please enter a full name.";
            }

            if (empty($new_email)) {
                $email_err = "Please enter an email.";
            } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                 $email_err = "Please enter a valid email format.";
            }
            
            // Check if email already exists for another user (optional but recommended)
            if(empty($email_err)){
                 $sql_check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
                 if($stmt_check = $conn->prepare($sql_check_email)){
                     $stmt_check->bind_param("si", $new_email, $user_id);
                     $stmt_check->execute();
                     $stmt_check->store_result();
                     if($stmt_check->num_rows > 0){
                         $email_err = "This email is already taken.";
                     }
                     $stmt_check->close();
                 }
            }

            // Check input errors before updating in database
            if (empty($name_err) && empty($email_err)) {
                // Prepare an update statement
                $sql_update = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";

                if ($stmt_update = $conn->prepare($sql_update)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt_update->bind_param("ssi", $new_full_name, $new_email, $user_id);

                    // Attempt to execute the prepared statement
                    if ($stmt_update->execute()) {
                        // Records updated successfully. Redirect to landing page
                        header("location: dashbord.php"); // Or redirect back to the user list section
                        exit();
                    } else {
                        echo "Error updating record: " . $conn->error; // Display specific error for debugging
                    }

                    // Close statement
                    $stmt_update->close();
                }
            } else {
                 // Display validation errors
                 echo "<div class='alert alert-danger'>";
                 if(!empty($name_err)){ echo "<p>" . $name_err . "</p>"; }
                 if(!empty($email_err)){ echo "<p>" . $email_err . "</p>"; }
                 echo "</div>";
            }
            
            // Re-fetch user data after attempting update to show current values in form
            if(isset($user_id)){
                 $sql = "SELECT id, full_name, email FROM users WHERE id = ?";
                 if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $param_id);
                    $param_id = $user_id;
                    if ($stmt->execute()) {
                       $result = $stmt->get_result();
                       if ($result->num_rows == 1) {
                            $user = $result->fetch_assoc();
                       }
                    }
                    $stmt->close();
                 }
            }
        }
        
        // Display edit form if user data is fetched
        if ($user) {
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="dashbord.php" class="btn btn-secondary">Cancel</a>
        </form>

        <?php
        }
        
        // Close database connection
        $conn->close();
        ?>
    </div>
</body>
</html> 