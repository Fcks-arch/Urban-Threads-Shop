<?php
session_start();


if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
   
    header('Location: admin_login.html');
    exit();
}

require_once 'db_connect.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = $_GET['id'];

    try {
        $pdo->beginTransaction();

        $stmt_cart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_cart->execute([$user_id]);

        $stmt_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt_user->execute([$user_id])) {
            $pdo->commit();
            $_SESSION['success_message'] = 'User and associated data deleted successfully.';
        } else {
            $pdo->rollBack();
            throw new Exception("Error executing delete user statement.");
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = 'Database error during user deletion: ' . $e->getMessage();
        error_log('Admin Delete User PDO Error: ' . $e->getMessage());
    } catch (Exception $e) {
         if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = 'An error occurred during user deletion: ' . $e->getMessage();
        error_log('Admin Delete User Error: ' . $e->getMessage());
    }
} else {
    $_SESSION['error_message'] = 'Invalid user ID for deletion.';
}

header('Location: admin/dashboard.php#users');
exit();
?> 