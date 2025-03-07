<?php
session_start();
require 'db_connect.php';

//check if admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

//get parameters
$action = $_GET['action'] ?? '';
$order_id = $_GET['order_id'] ?? '';
if ($action === 'ship') {
    $stmt = $pdo->prepare("UPDATE orders SET status='shipped' WHERE order_id = ?");
    $stmt->execute([$order_id]);
}elseif ($action === 'cancel') {
    $stmt = $pdo->prepare("UPDATE orders SET status='cancelled' WHERE order_id = ?");
    $stmt->execute([$order_id]);
}

//redirect back
header("Location: admin_dashboard.php");
exit;
