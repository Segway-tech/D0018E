

<?php
session_start();
require 'db_connect.php';

//make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $asset_id = $_POST['asset_id'];

    //check if asset is already in the cart, if it is then update quantity
    $stmt = $pdo->prepare("SELECT * FROM shopping_cart WHERE user_id = ? AND asset_id = ?");
    $stmt->execute([$user_id, $asset_id]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        //update quantity, increment by 1
        $stmt = $pdo->prepare("UPDATE shopping_cart SET quantity = quantity + 1 WHERE cart_id = ?");
        $stmt->execute([$cartItem['cart_id']]);
    } else {
        //insert new cart item
        $stmt = $pdo->prepare("INSERT INTO shopping_cart (user_id, asset_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $asset_id]);
    }
}

//redirect back to the store page (or a cart page)
header("Location: index.php");
exit;
?>
