<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db_connect.php';

//make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    //retrieve cart items joining with assets details from shopping_cart table
    $stmt = $pdo->prepare("SELECT c.cart_id, c.quantity, a.* FROM shopping_cart c JOIN assets a ON c.asset_id = a.asset_id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" type="text/css" href="css1.css" />
    <style>
      table {
        border-collapse: collapse;
        width: 70%;
        margin: 40px auto;
        background-color: #222;
      }
      table, th, td {
        border: 1px solid #555;
      }
      th, td {
        padding: 10px;
        text-align: left;
      }
      th {
        background-color: #333;
        color: #fff;
      }
      td {
        color: #fff;
      }
      p {
        text-align: center;
        color: #fff;
      }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Your Shopping Cart</h2>
    <?php if (!empty($cartItems)): ?>
        <table>
            <tr>
                <th>Title</th>
                <th>Platform</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Remove</th>
            </tr>
            <?php
            $grandTotal = 0;
            foreach ($cartItems as $item):
                $total = $item['price'] * $item['quantity'];
                $grandTotal += $total;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['videogame_title']) ?></td>
                <td><?= htmlspecialchars($item['platform']) ?></td>
                <td><?= htmlspecialchars($item['price']) ?></td>
                <td><?= htmlspecialchars($item['quantity']) ?></td>
                <td><?= number_format($total, 2) ?></td>
                <td>
                    <!-- Remove item form -->
                    <form action="remove_from_cart.php" method="post">
                        <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                        <input type="submit" value="Remove">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h3 style="text-align:center;">Grand Total: <?= number_format($grandTotal, 2) ?></h3>
    <?php else: ?>
        <p>Your cart is empty. Please add an item to proceed to checkout.</p>
    <?php endif; ?>
    <p style="text-align:center;"><a href="index.php">Back to Store</a></p>
</body>
</html>
