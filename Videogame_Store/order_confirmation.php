





<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['order_id'];

//fetch order info
$stmt = $pdo->prepare("
    SELECT o.*, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found or not yours.");
}

$stmt = $pdo->prepare("
    SELECT od.*, a.videogame_title
    FROM order_details od
    JOIN assets a ON od.asset_id = a.asset_id
    WHERE od.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>

    <!-- same style as index -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css1.css" />

    <style>
      .vertical-line {
          position: absolute;
          top: 0;
          left: 2%;
          width: 5px;
          height: 100%;
          background-color: #00FF00;
          z-index: 0;
      }

      .nav, header {
          position: relative;
          z-index: 1;
      }

      .content-area {
        width: 70%;
        margin: 40px auto;
        background-color: #111;
        padding: 20px;
        border: 2px solid #00FF00;
      }

      h1, h2 {
        text-align: center;
      }

      table {
        border-collapse: collapse;
        width: 100%;
        margin: 20px 0;
        background-color: #222;
      }
      table, th, td {
        border: 1px solid #555;
      }
      th, td {
        padding: 10px;
        text-align: left;
        color: #fff;
      }
      th {
        background-color: #333;
      }

      .back-link {
        text-align: center;
        margin-top: 20px;
      }
    </style>
</head>
<body>
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
        <div class="nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <a href="index.php">Back to Store</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </header>
    <div class="blue-line"></div>
    <div class="vertical-line"></div>

    <div class="content-area">
      <h1>Order #<?= htmlspecialchars($order_id) ?> - Confirmation</h1>
      <p>Status: <?= htmlspecialchars($order['status']) ?></p>
      <p>Total Amount: <?= htmlspecialchars($order['total_amount']) ?></p>
      <p>Order Date: <?= htmlspecialchars($order['order_date']) ?></p>

      <h2>Items</h2>
      <table>
        <tr>
          <th>Game</th>
          <th>Quantity</th>
          <th>Price</th> 
        </tr>
        <?php foreach ($items as $itm): ?>
        <tr>
          <td><?= htmlspecialchars($itm['videogame_title']) ?></td>
          <td><?= htmlspecialchars($itm['quantity']) ?></td>
          <td><?= htmlspecialchars($itm['checkout_price']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>

      <p style="text-align:center;">Thank you for buying!</p>
      <div class="back-link">
        <a href="index.php">← Back to store</a>
      </div>
    </div>
</body>
</html>
