<?php
session_start();
require 'db_connect.php';

//user must be logged in to leave review
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $asset_id = $_POST['asset_id'];
    $rating = (int)$_POST['rating'];  //cast to int
    $comment = trim($_POST['comment']);
    //validation
    if ($rating < 1 || $rating > 5) {
        die("Invalid rating.");
    }
    //insert into reviews
    $stmt = $pdo->prepare("
        INSERT INTO reviews (user_id, asset_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $asset_id, $rating, $comment]);

    //redirect back
    header("Location: asset_detail.php?asset_id=" . $asset_id);
    exit;} 
else{
    header("Location: index.php");
    exit;
}
