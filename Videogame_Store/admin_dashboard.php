


<?php
session_start();
require 'db_connect.php';

//check if user is admin
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

// admin can see pending orders, etc.
$ordersStmt = $pdo->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC
");
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$usersStmt = $pdo->query("SELECT * FROM users WHERE role='user'");
$allUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!-- Same styling as index.php -->
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

      h1, h2 {
        text-align: center;
      }
    </style>
</head>
<body>
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
        <div class="nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <!-- We can add a link back to index, or admin-specific links here if desired -->
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

    <h1>Welcome, Admin!</h1>

    <h2>Manage Orders</h2>
    <table>
        <tr>
          <th>Order ID</th>
          <th>Username</th>
          <th>Status</th>
          <th>Total</th>
          <th>Actions</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['order_id'] ?></td>
            <td><?= htmlspecialchars($order['username']) ?></td>
            <td><?= htmlspecialchars($order['status']) ?></td>
            <td><?= htmlspecialchars($order['total_amount']) ?></td>
            <td>
                <?php if ($order['status'] === 'pending'): ?>
                    <a href="admin_order_action.php?action=ship&order_id=<?= $order['order_id'] ?>">Ship</a> |
                    <a href="admin_order_action.php?action=cancel&order_id=<?= $order['order_id'] ?>">Cancel</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Manage Users</h2>
    <table>
        <tr><th>User ID</th><th>Username</th><th>Email</th><th>Actions</th></tr>
        <?php foreach ($allUsers as $u): ?>
        <tr>
            <td><?= $u['user_id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <a href="admin_remove_user.php?user_id=<?= $u['user_id'] ?>">Remove</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
