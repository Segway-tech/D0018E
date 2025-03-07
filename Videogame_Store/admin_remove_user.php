<?php
session_start();
require 'db_connect.php';

//check if admin
if (!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$adminRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminRow||$adminRow['role']!=='admin') {
    header("Location: index.php");
    exit;
}

//user removal
if (isset($_GET['user_id'])) {
    $removeId = (int)$_GET['user_id'];

    //remove items from users cart
    $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
    $stmt->execute([$removeId]);

    // remove from order_details
    //do join based deletion
    $stmt = $pdo->prepare("
        DELETE od
        FROM order_details od
        JOIN orders o ON od.order_id = o.order_id
        WHERE o.user_id = ?
    ");
    $stmt->execute([$removeId]);

    //remove from orders
    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
    $stmt->execute([$removeId]);

    //remove review from this user
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
    $stmt->execute([$removeId]);

    //remove the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$removeId]);
}

//back to admin dashboard
header("Location: admin_dashboard.php");
exit;
