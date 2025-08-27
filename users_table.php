<?php

// This file contains the HTML and PHP to display the users table

include 'db_connect.php'; // Include your database connection

$sql = "SELECT id, full_name, email FROM users";
$result = $conn->query($sql);

?>

<input type="text" id="search" class="form-control mb-3" placeholder="Search users...">

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="userTable">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>
                        <a href='edit_user.php?id=" . urlencode($row['id']) . "' class='btn btn-warning btn-sm'>Edit</a>
                        <a href='delete.php?id=" . urlencode($row['id']) . "' class='btn btn-danger btn-sm delete-user'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>No users found</td></tr>";
        }
        $conn->close();
        ?>
    </tbody>
</table>

<script>
    // Basic search functionality for users
    document.getElementById('search').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#userTable tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Add confirmation for delete links using JavaScript event delegation
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete-user')) {
            if (!confirm('Are you sure you want to delete this user?')) {
                e.preventDefault(); // Prevent the default link action if the user cancels
            }
        }
    });
</script> 