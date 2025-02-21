<?php
// db_connect.php

//credentials
$host = 'localhost';
$db   = 'videogames_db';
$user = 'videogames_user';
$pass = 'VideoGamesHaha123!';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    //create PDO instance
    $pdo = new PDO($dsn, $user, $pass);
    //PDO error mode to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    //if connection fails
    die("Database connection failed: " . $e->getMessage());
}
?>
