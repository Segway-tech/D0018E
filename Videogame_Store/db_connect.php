<?php
// db_connect.php

// Database credentials
$host = 'localhost';
$db   = 'videogames_db';
$user = 'videogames_user';
$pass = 'VideoGamesHaha123!';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    // Create a PDO instance
    $pdo = new PDO($dsn, $user, $pass);
    // Enable PDO error mode to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, stop execution and display error
    die("Database connection failed: " . $e->getMessage());
}
?>
