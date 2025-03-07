<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

//make sure request is POST and cart id provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];

    try {
        //delete item from shopping cart table
        $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

//redirect back to view cart page
header("Location: view_cart.php");
exit;
?>
