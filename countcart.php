<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["count" => 0]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode(["count" => $row['total'] ?? 0]);
} catch(Exception $e) {
    echo json_encode(["count" => 0, "error" => $e->getMessage()]);
}
?>
