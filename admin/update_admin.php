<?php

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../admin_login.html'); 
}

require_once '../db_connect.php';


if (!isset($conn) || $conn->connect_error) {
     die('Database connection failed: ' . $conn->connect_error); 
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $admin_id = $_GET['id'];

    if ($admin_id == $_SESSION['user_id'] && $_SESSION['role'] === 'super_admin') {
         echo "You cannot edit your own super admin account details via this page.";
    } else {

        echo "Update Administrator Page for Admin ID: " . htmlspecialchars($admin_id);
    }

} else {
    die('Invalid request: Administrator ID not specified.');
}

$conn->close();

?> 