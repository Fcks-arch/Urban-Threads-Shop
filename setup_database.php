<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Create connection without database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS loginusers");
    $pdo->exec("USE loginusers");
    
    // Create tables
    $pdo->exec(file_get_contents('create_tables.sql'));
    
    // Insert products
    $pdo->exec(file_get_contents('insert_products.sql'));
    
    echo "Database setup completed successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 