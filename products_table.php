<?php

// This file contains the HTML and PHP to display the products table

include 'db_connect.php'; // Include your database connection

// Assume your products table is named 'products' and has columns id, name, price, and image_url
$sql = "SELECT id, name, price, image_url FROM products";
$result = $conn->query($sql);

?>

<input type="text" id="searchProducts" class="form-control mb-3" placeholder="Search products...">

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="productsTable">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>₱" . htmlspecialchars(number_format($row['price'], 2)) . "</td>";
                echo "<td><img src='" . htmlspecialchars($row['image_url']) . "' alt='Product Image' style='width: 50px; height: auto;'></td>"; // Basic image display
                echo "<td>
                        <a href='edit_product.php?id=" . urlencode($row['id']) . "' class='btn btn-warning btn-sm'>Edit</a>
                        <a href='delete_product.php?id=" . urlencode($row['id']) . "' class='btn btn-danger btn-sm delete-product'>Delete</a>
                      </td>"; // Added class delete-product
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>No products found</td></tr>";
        }
        // Note: Database connection is closed in dashbord.php or other main file if included there.
        // $conn->close(); 
        ?>
    </tbody>
</table>

<script>
    // Basic search functionality for products
    document.getElementById('searchProducts').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#productsTable tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Add confirmation for delete product links using JavaScript event delegation
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete-product')) {
            if (!confirm('Are you sure you want to delete this product?')) {
                e.preventDefault(); // Prevent the default link action if the user cancels
            }
        }
    });
</script> 