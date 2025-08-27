<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Auth Test</title>
</head>
<body>
    <h1>Authentication Test</h1>
    
    <h2>Current Session Status</h2>
    <p>Session ID: <?php echo session_id(); ?></p>
    <p>Logged In: <?php echo isset($_SESSION['user_id']) ? 'YES (User ID: ' . $_SESSION['user_id'] . ')' : 'NO'; ?></p>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <h2>User Information</h2>
        <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
        <p><a href="logout.php">Logout</a></p>
        <p><a href="checkout.html">Test Checkout</a></p>
    <?php else: ?>
        <h2>Login Form</h2>
        <form action="test_signin.php" method="post">
            <p>Email: <input type="email" name="email" required></p>
            <p>Password: <input type="password" name="password" required></p>
            <p><input type="submit" value="Login"></p>
        </form>
        
        <h2>Or Test Direct</h2>
        <p><a href="signin.html">Go to Sign In Page</a></p>
        <p><a href="signup.html">Go to Sign Up Page</a></p>
    <?php endif; ?>
    
    <h2>Debug Links</h2>
    <p><a href="check_login.php" target="_blank">Test check_login.php</a></p>
    <p><a href="shop2.html">Go to Shop</a></p>
</body>
</html>

