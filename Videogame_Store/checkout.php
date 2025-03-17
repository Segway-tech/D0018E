


<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

//get cart items
$stmt = $pdo->prepare("
    SELECT c.cart_id, c.quantity, a.asset_id, a.price
    FROM shopping_cart c
    JOIN assets a ON c.asset_id = a.asset_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)){
    //if cart empty then redirect
     ?>
    <!DOCTYPE html>
    <html>
    <head>
      <title>Checkout - Very Cool Videogame E-shop</title>
      <link rel="stylesheet" href="css1.css">
    </head>
    <body>
      <p>Your cart is empty. <a href="index.php">Go back to store</a>.</p>
    </body>
    </html>
    <?php
    exit;
}

//calculate total
$grandTotal = 0;
foreach ($cartItems as $item) {
    $grandTotal += ($item['quantity'] * $item['price']);
}


//before creating an order make sure there is enough stock for each item
foreach ($cartItems as $item) {
    $asset_id = $item['asset_id'];
    $quantity = (int)$item['quantity'];

    //fetch current stock
    $stmtStock = $pdo->prepare("SELECT stock_amount FROM assets WHERE asset_id = ?");
    $stmtStock->execute([$asset_id]);
    $row = $stmtStock->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Error: Asset not found during checkout.");
    }
    $currentStock = (int)$row['stock_amount'];

    //if not enough stock for this item show error
    if ($quantity > $currentStock) {
        die("Not enough stock for one of your cart items. Please adjust your cart!");
    }

    //otherwise decrement the stock by the carts quantity
    $newStock = $currentStock - $quantity;
    $stmtUpdate = $pdo->prepare("UPDATE assets SET stock_amount = ? WHERE asset_id = ?");
    $stmtUpdate->execute([$newStock, $asset_id]);
}

//insert into orders
$stmt = $pdo->prepare("
    INSERT INTO orders (user_id, order_date, status, total_amount)
    VALUES (?, NOW(), 'pending', ?)
");
$stmt->execute([$user_id, $grandTotal]);
$order_id = $pdo->lastInsertId();

//insert into order_details
foreach ($cartItems as $item) {
    $priceAtCheckout = $item['price'];
    $stmt = $pdo->prepare("
        INSERT INTO order_details (order_id, asset_id, quantity, checkout_price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$order_id, $item['asset_id'], $item['quantity'], $priceAtCheckout]);
}

//clear the cart
$stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
$stmt->execute([$user_id]);

//redirect to confirmation
header("Location: order_confirmation.php?order_id=$order_id");
exit;
