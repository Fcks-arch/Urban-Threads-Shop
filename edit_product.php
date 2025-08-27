<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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
        <h2>Edit Product</h2>

        <?php
        // Include database connection
        include 'db_connect.php';

        $product = null;

        // Check if ID is provided in the URL
        if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
            // Prepare a select statement
            $sql = "SELECT id, name, price, image_url FROM products WHERE id = ?";

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
                        $product = $result->fetch_assoc();
                    } else {
                        // URL doesn't contain valid id. Redirect to error page or product list.
                        echo "<div class='alert alert-danger'>Product not found.</div>";
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        } else {
            // URL doesn't contain id parameter. Redirect to error page or product list.
            echo "<div class='alert alert-danger'>Invalid request.</div>";
        }

        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get hidden input value for product ID
            $product_id = $_POST['product_id'];
            $new_name = trim($_POST['name']);
            $new_price = trim($_POST['price']);
            $new_image_url = trim($_POST['image_url']);

            // Validate inputs (add more robust validation as needed)
            if (empty($new_name)) {
                $name_err = "Please enter a product name.";
            }

            if (empty($new_price)) {
                $price_err = "Please enter a price.";
            } elseif (!is_numeric($new_price) || $new_price < 0) {
                $price_err = "Please enter a valid positive price.";
            }

            if (empty($new_image_url)) {
                 $image_url_err = "Please enter an image URL.";
            } elseif (!filter_var($new_image_url, FILTER_VALIDATE_URL)) {
                 $image_url_err = "Please enter a valid URL format.";
            }

            // Check input errors before updating in database
            if (empty($name_err) && empty($price_err) && empty($image_url_err)) {
                // Prepare an update statement
                $sql_update = "UPDATE products SET name = ?, price = ?, image_url = ? WHERE id = ?";

                if ($stmt_update = $conn->prepare($sql_update)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt_update->bind_param("sssi", $new_name, $new_price, $new_image_url, $product_id);

                    // Attempt to execute the prepared statement
                    if ($stmt_update->execute()) {
                        // Records updated successfully. Redirect to dashboard or product list section
                        header("location: dashbord.php#products-section"); 
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
                 if(!empty($price_err)){ echo "<p>" . $price_err . "</p>"; }
                 if(!empty($image_url_err)){ echo "<p>" . $image_url_err . "</p>"; }
                 echo "</div>";
            }
            
             // Re-fetch product data after attempting update to show current values in form
            if(isset($product_id)){
                 $sql = "SELECT id, name, price, image_url FROM products WHERE id = ?";
                 if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $param_id);
                    $param_id = $product_id;
                    if ($stmt->execute()) {
                       $result = $stmt->get_result();
                       if ($result->num_rows == 1) {
                            $product = $result->fetch_assoc();
                       }
                    }
                    $stmt->close();
                 }
            }
        }

        // Display edit form if product data is fetched
        if ($product) {
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="text" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
             <div class="mb-3">
                <label for="image_url" class="form-label">Image URL</label>
                <input type="text" name="image_url" id="image_url" class="form-control" value="<?php echo htmlspecialchars($product['image_url']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="dashbord.php#products-section" class="btn btn-secondary">Cancel</a>
        </form>

        <?php
        }
        
        // Close database connection
        $conn->close();
        ?>
    </div>
</body>
</html> 