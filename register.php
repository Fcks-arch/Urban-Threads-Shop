<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    http_response_code(405);
    die("405 Method Not Allowed");
}

$email = $_POST["email"] ?? "";
$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format.");
}

if (empty($email) || empty($username) || empty($password)) {
    die("All fields are required.");
}

$check_sql = "SELECT id FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    die("Username already exists!");
}
$check_stmt->close();

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $email, $username, $password_hash);

if ($stmt->execute()) {
    // Redirect back to index.html with a success query
    header("Location: signin.html?registered=true");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
