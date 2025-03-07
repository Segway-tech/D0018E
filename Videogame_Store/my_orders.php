


<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

/* clear order logic */
if (isset($_GET['action']) && $_GET['action'] === 'clear' && isset($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];

    //verify the order belongs to this user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$orderId, $user_id]);
    $existingOrder = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingOrder) {
        //remove order_details
        $stmt = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt->execute([$orderId]);

        //remove the order itself
        $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ? AND user_id = ?");
        $stmt->execute([$orderId, $user_id]);
    }

    //redirect back
    header("Location: my_orders.php");
    exit;
}

/*fetch all orders for this specific user*/
$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>

  <!-- same as index -->
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

    h1 {
      text-align: center;
      margin-top: 30px;
    }

    table {
      border-collapse: collapse;
      width: 80%;
      margin: 30px auto;
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

    .clear-btn {
      color: #fff;
      text-decoration: none;
      border: 1px solid #0f0;
      padding: 3px 6px;
      margin-left: 10px;
      background-color: #000;
    }
    .clear-btn:hover {
      text-shadow: 0 0 5px #00FF00;
      cursor: pointer;
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

  <h1>My Orders</h1>
  <?php if (!empty($orders)): ?>
  <table>
    <tr>
      <th>Order ID</th>
      <th>Date</th>
      <th>Status</th>
      <th>Total</th>
      <th>Action</th>
    </tr>
    <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= $o['order_id'] ?></td>
      <td><?= htmlspecialchars($o['order_date']) ?></td>
      <td><?= htmlspecialchars($o['status']) ?></td>
      <td><?= htmlspecialchars($o['total_amount']) ?></td>
      <td>
        <a href="order_confirmation.php?order_id=<?= $o['order_id'] ?>">View</a>
        <!--clear link for every order -->
        <a class="clear-btn"
           href="my_orders.php?action=clear&order_id=<?= $o['order_id'] ?>"
           onclick="return confirm('Are you sure you want to remove this order?');">
           Clear
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
    <p style="text-align:center;">You have no orders yet.</p>
  <?php endif; ?>

</body>
</html>
