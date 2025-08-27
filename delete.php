<?php
    if(isset($_POST["id"]) && !empty($_POST["id"])){
    require_once "connect.php";

$sql = "DELETE FROM users WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){

    mysqli_stmt_bind_param($stmt, "i", $param_id);

$param_id = trim($_POST["id"]);

    if(mysqli_stmt_execute($stmt)){

    header("location: dashbord.php");
    exit();
    } else{
    echo "Oops! Something went wrong. Please try again later.";
    }
}

mysqli_stmt_close($stmt);

mysqli_close($conn);
    } else{

    if(empty(trim($_GET["id"]))){

    header("location: error.php");
    exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Record</title>
    <link rel="stylesheet"
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    .wrapper{
    width: 600px;
    margin: 0 auto;
    }
    
    </style>

</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                <h2 class="mt-5 mb-3">Delete Record</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="alert alert-danger">
        <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
        <p>Are you sure you want to delete this employee record?</p>
        <input type="submit" value="Yes" class="btn btn-danger">
        <a href="dashbord.php" class="btn btn-secondary">No</a>
        </div>
</form>
    </div>
</div>
</div>
</div>
</body>
</html>