<?php

// This file contains the HTML and PHP to display the pending orders table

include 'db_connect.php'; // Include your database connection

// Fetch pending orders (where status is 'Order Placed')
$sql = "SELECT id, order_number, full_name, total_amount, status, order_date FROM orders WHERE status = 'Order Placed' ORDER BY order_date DESC";
$result = $conn->query($sql);

?>

<h4>Pending Orders</h4>

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Order ID</th>
            <th>Order Number</th>
            <th>Customer Name</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['order_number']) . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>₱" . htmlspecialchars(number_format($row['total_amount'], 2)) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
                echo "<td>
                        <a href='approve_order.php?id=" . urlencode($row['id']) . "' class='btn btn-success btn-sm'>Approve</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>No pending orders found</td></tr>";
        }
        // Note: Database connection is closed in dashbord.php or other main file if included there.
        // $conn->close(); 
        ?>
    </tbody>
</table> 