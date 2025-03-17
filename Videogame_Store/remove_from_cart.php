
<?php
//for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

//make sure request is POST and cart_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = (int)$_POST['cart_id'];

    try {

        // delete item from shopping_cart table
        $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE cart_id = ?");
        $stmt->execute([$cart_id]);

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

//redirect back to view cart page
header("Location: view_cart.php");
exit;
